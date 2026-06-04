const svg = document.querySelector("#map");
const summary = document.querySelector("#summary");
const roomList = document.querySelector("#room-list");
const pointList = document.querySelector("#point-list");
const details = document.querySelector("#point-details");
const clearButton = document.querySelector("#clear-selection");
const mapWrap = document.querySelector(".map-wrap");

const minZoom = 1;
const maxZoom = 4;
const zoomStep = 0.25;
const routeManifestUrls = [
    "../../routes/manifest.json",
    "routes.json",
];

let data = null;
let pointsById = new Map();
let selectedPointId = null;
let selectedRoomId = null;
let outgoingIds = new Set();
let outgoingRoleLabels = new Map();
let incomingIds = new Set();
let rewireIds = new Set();
let mapZoom = 1;
let mapViewWidth = 1;
let mapViewHeight = 1;

init();

async function init() {
    data = await loadData();
    pointsById = new Map(data.points.map((point) => [point.id, point]));

    summary.textContent = [
        `${data.rooms.length} rooms`,
        `${data.points.length} points`,
        `${countEdges()} links`,
        `${data.rewires.length} rewires`,
    ].join(" / ");

    renderRoomsList();
    renderPointsList();
    renderMap();
    updateMapSize();

    clearButton.addEventListener("click", () => {
        selectPoint(null);
    });
    mapWrap.addEventListener("wheel", handleMapWheel, { passive: false });
    window.addEventListener("resize", updateMapSize);
}

async function loadData() {
    if (location.protocol !== "file:") {
        for (const url of routeManifestUrls) {
            try {
                const response = await fetch(cacheBustedUrl(url));
                if (response.ok) {
                    return hydrateRouteData(await response.json(), response.url);
                }
            } catch (_error) {
                // The generated script below keeps the viewer usable from file URLs.
            }
        }
    }

    if (window.ROUTE_MAP_DATA) {
        return hydrateRouteData(window.ROUTE_MAP_DATA, "../../routes/manifest.json");
    }

    throw new Error("Route data is unavailable.");
}

async function hydrateRouteData(routeData, manifestUrl) {
    if (Array.isArray(routeData.points)) {
        return routeData;
    }

    if (!Array.isArray(routeData.routeFiles)) {
        throw new Error("Route data has neither points nor routeFiles.");
    }

    const rooms = await Promise.all(routeData.routeFiles.map((routeFile) => loadRouteFile(routeFile, manifestUrl)));
    return {
        ...routeData,
        points: rooms.flatMap((room) => room.points ?? []),
    };
}

async function loadRouteFile(routeFile, manifestUrl) {
    const file = typeof routeFile === "string" ? routeFile : routeFile.file;
    const response = await fetch(cacheBustedUrl(new URL(file, manifestUrl).href));
    if (!response.ok) {
        throw new Error(`Cannot load route file: ${file}`);
    }

    return response.json();
}

function cacheBustedUrl(url) {
    const separator = url.includes("?") ? "&" : "?";
    return `${url}${separator}time=${Date.now()}`;
}

function countEdges() {
    return data.points.reduce((total, point) => {
        return total + [point.topLeft, point.bottomRight, point.alternative].filter(Boolean).length;
    }, 0);
}

function renderRoomsList() {
    roomList.replaceChildren();

    for (const room of data.rooms) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "list-button";
        button.textContent = room.id;
        button.dataset.roomId = room.id;
        button.addEventListener("click", () => {
            selectedRoomId = selectedRoomId === room.id ? null : room.id;
            applySelectionClasses();
        });
        roomList.append(button);
    }
}

function renderPointsList() {
    pointList.replaceChildren();

    for (const point of data.points) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "list-button point-row";
        button.dataset.pointId = point.id;
        button.addEventListener("click", () => selectPoint(point.id));

        const name = document.createElement("span");
        name.className = "point-name";
        name.textContent = point.id;

        const meta = document.createElement("span");
        meta.className = "point-meta";
        meta.textContent = `${point.roomX},${point.roomY} @ ${point.x},${point.y}`;

        button.append(name, meta);
        pointList.append(button);
    }
}

function renderMap() {
    const totalWidth = data.room.amountX * data.room.width;
    const totalHeight = data.room.amountY * data.room.height;
    mapViewWidth = totalWidth + 4;
    mapViewHeight = totalHeight + 4;
    svg.setAttribute("viewBox", `-2 -2 ${mapViewWidth} ${mapViewHeight}`);
    svg.replaceChildren();

    const roomsLayer = svgElement("g", { class: "rooms-layer" });
    const edgesLayer = svgElement("g", { class: "edges-layer" });
    const pointsLayer = svgElement("g", { class: "points-layer" });

    for (const room of data.rooms) {
        const x = room.x * data.room.width;
        const y = room.y * data.room.height;

        roomsLayer.append(svgElement("rect", {
            class: "room-rect",
            x,
            y,
            width: data.room.width,
            height: data.room.height,
            "data-room-id": room.id,
        }));

        const label = svgElement("text", {
            class: "room-label",
            x: x + 1.1,
            y: y + 3.1,
        });
        label.textContent = room.id;
        roomsLayer.append(label);
    }

    for (const edge of buildEdges()) {
        const from = pointsById.get(edge.from);
        const to = pointsById.get(edge.to);
        if (!from || !to) {
            continue;
        }

        edgesLayer.append(svgElement("line", {
            class: edgeClass(edge),
            x1: globalX(from),
            y1: globalY(from),
            x2: globalX(to),
            y2: globalY(to),
            "data-from": edge.from,
            "data-to": edge.to,
            "data-role": edge.role,
        }));
    }

    for (const point of data.points) {
        const group = svgElement("g", {
            class: "point-group",
            "data-point-id": point.id,
        });

        const marker = svgElement("circle", {
            class: `point point-${point.type}`,
            cx: globalX(point),
            cy: globalY(point),
            r: pointRadius(point),
            tabindex: "0",
            "data-point-id": point.id,
        });

        const label = svgElement("text", {
            class: "point-label",
            x: globalX(point) + 1.1,
            y: globalY(point) - 1.1,
        });
        label.textContent = shortPointName(point.id);

        const roleLabel = svgElement("text", {
            class: "point-link-role",
            x: globalX(point),
            y: globalY(point),
        });

        group.append(marker, roleLabel, label);
        group.addEventListener("click", () => selectPoint(point.id));
        group.addEventListener("keydown", (event) => {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                selectPoint(point.id);
            }
        });
        pointsLayer.append(group);
    }

    svg.append(roomsLayer, edgesLayer, pointsLayer);
    applySelectionClasses();
}

function handleMapWheel(event) {
    event.preventDefault();

    const previousZoom = mapZoom;
    const direction = event.deltaY < 0 ? 1 : -1;
    mapZoom = clampZoom(mapZoom + direction * zoomStep);

    if (mapZoom === previousZoom) {
        return;
    }

    const rect = mapWrap.getBoundingClientRect();
    const focusX = event.clientX - rect.left + mapWrap.scrollLeft;
    const focusY = event.clientY - rect.top + mapWrap.scrollTop;
    const scaleRatio = mapZoom / previousZoom;

    updateMapSize();

    mapWrap.scrollLeft = focusX * scaleRatio - (event.clientX - rect.left);
    mapWrap.scrollTop = focusY * scaleRatio - (event.clientY - rect.top);
}

function clampZoom(value) {
    const stepped = Math.round(value / zoomStep) * zoomStep;
    return Math.min(maxZoom, Math.max(minZoom, stepped));
}

function updateMapSize() {
    const fitScale = Math.min(
        mapWrap.clientWidth / mapViewWidth,
        mapWrap.clientHeight / mapViewHeight
    );
    const scaledWidth = mapViewWidth * fitScale * mapZoom;
    const scaledHeight = mapViewHeight * fitScale * mapZoom;

    svg.style.width = `${scaledWidth}px`;
    svg.style.height = `${scaledHeight}px`;
}

function buildEdges() {
    const edges = [];

    for (const point of data.points) {
        addEdge(edges, point.id, point.topLeft, "topLeft");
        addEdge(edges, point.id, point.bottomRight, "bottomRight");
        addEdge(edges, point.id, point.alternative, "alternative");
    }

    for (const rewire of data.rewires) {
        addEdge(edges, rewire.from, rewire.to, "rewire");
    }

    return edges;
}

function addEdge(edges, from, to, role) {
    if (!to) {
        return;
    }

    edges.push({ from, to, role });
}

function edgeClass(edge) {
    const classes = ["edge"];
    if (edge.role === "alternative") {
        classes.push("is-alternative");
    }
    if (edge.role === "rewire") {
        classes.push("is-rewire");
    }
    return classes.join(" ");
}

function selectPoint(pointId) {
    selectedPointId = pointId;
    selectedRoomId = pointId ? roomId(pointsById.get(pointId)) : null;
    recomputeRelatedPoints();
    renderDetails();
    applySelectionClasses();
}

function recomputeRelatedPoints() {
    outgoingIds = new Set();
    outgoingRoleLabels = new Map();
    incomingIds = new Set();
    rewireIds = new Set();

    if (!selectedPointId) {
        return;
    }

    const selected = pointsById.get(selectedPointId);
    addOutgoingRole(selected.topLeft, "L");
    addOutgoingRole(selected.bottomRight, "R");
    addOutgoingRole(selected.alternative, "A");

    for (const point of data.points) {
        if ([point.topLeft, point.bottomRight, point.alternative].includes(selectedPointId)) {
            incomingIds.add(point.id);
        }
    }

    for (const rewire of data.rewires) {
        if (rewire.from === selectedPointId) {
            rewireIds.add(rewire.to);
        }
        if (rewire.to === selectedPointId) {
            rewireIds.add(rewire.from);
        }
    }
}

function addOutgoingRole(pointId, label) {
    if (!pointId) {
        return;
    }

    outgoingIds.add(pointId);

    const existingLabel = outgoingRoleLabels.get(pointId) ?? "";
    if (!existingLabel.includes(label)) {
        outgoingRoleLabels.set(pointId, `${existingLabel}${label}`);
    }
}

function applySelectionClasses() {
    let selectedGroup = null;

    document.querySelectorAll(".point-group").forEach((group) => {
        const pointId = group.dataset.pointId;
        const point = pointsById.get(pointId);
        const isSelected = pointId === selectedPointId;
        const roleText = outgoingRoleLabels.get(pointId) ?? "";
        group.classList.toggle("is-selected", isSelected);
        group.classList.toggle("is-related", isRelatedPoint(pointId));

        const marker = group.querySelector(".point");
        marker.classList.toggle("is-selected", isSelected);
        marker.classList.toggle("is-related", outgoingIds.has(pointId) || rewireIds.has(pointId));
        marker.classList.toggle("is-incoming", incomingIds.has(pointId));
        marker.classList.toggle("is-direct-outgoing", roleText.includes("L") || roleText.includes("R"));
        marker.classList.toggle("is-room-muted", selectedRoomId && roomId(point) !== selectedRoomId);

        const roleLabel = group.querySelector(".point-link-role");
        roleLabel.textContent = roleText;
        roleLabel.classList.toggle("is-active", roleText !== "");

        if (isSelected) {
            selectedGroup = group;
        }
    });

    if (selectedGroup) {
        selectedGroup.parentNode.append(selectedGroup);
    }

    document.querySelectorAll("[data-point-id].list-button").forEach((button) => {
        const pointId = button.dataset.pointId;
        button.classList.toggle("is-selected", pointId === selectedPointId);
        button.classList.toggle("is-related", isRelatedPoint(pointId));
    });

    document.querySelectorAll(".room-rect").forEach((rect) => {
        rect.classList.toggle("is-selected-room", rect.dataset.roomId === selectedRoomId);
    });

    document.querySelectorAll("[data-room-id].list-button").forEach((button) => {
        button.classList.toggle("is-selected", button.dataset.roomId === selectedRoomId);
    });

    document.querySelectorAll(".edge").forEach((edge) => {
        const from = edge.dataset.from;
        const to = edge.dataset.to;
        edge.classList.toggle("is-active", selectedPointId && (from === selectedPointId || to === selectedPointId));
    });
}

function isRelatedPoint(pointId) {
    return outgoingIds.has(pointId) || incomingIds.has(pointId) || rewireIds.has(pointId);
}

function renderDetails() {
    if (!selectedPointId) {
        details.className = "empty-state";
        details.textContent = "No point selected.";
        return;
    }

    const point = pointsById.get(selectedPointId);
    details.className = "detail-grid";
    details.replaceChildren(
        detailName("id"), detailValue(point.id),
        detailName("room"), detailValue(`${point.roomX},${point.roomY}`),
        detailName("position"), detailValue(`${point.x},${point.y}`),
        detailName("type"), detailValue(point.type),
        detailName("topLeft"), detailLinks([{ id: point.topLeft, role: "direct" }]),
        detailName("bottomRight"), detailLinks([{ id: point.bottomRight, role: "direct" }]),
        detailName("alternative"), detailLinks([{ id: point.alternative, role: "alternative" }]),
        detailName("incoming"), detailLinks([...incomingIds].map((id) => ({ id, role: "direct" }))),
        detailName("rewires"), detailLinks(rewiresFor(point.id)),
        detailName("source"), detailValue(`${point.sourceFile}:${point.sourceLine}`)
    );
}

function detailName(text) {
    const element = document.createElement("div");
    element.className = "field-name";
    element.textContent = text;
    return element;
}

function detailValue(text) {
    const element = document.createElement("div");
    element.className = "detail-value";
    element.textContent = text;
    return element;
}

function detailLinks(links) {
    const element = document.createElement("div");
    element.className = "detail-value";

    const filtered = links.filter((link) => link.id);
    if (filtered.length === 0) {
        element.textContent = "0";
        return element;
    }

    for (const link of filtered) {
        const pill = document.createElement("button");
        pill.type = "button";
        pill.className = `link-pill ${link.role === "alternative" ? "is-alternative" : ""} ${link.role === "rewire" ? "is-rewire" : ""}`;
        pill.textContent = link.label ?? link.id;
        pill.addEventListener("click", () => selectPoint(link.id));
        element.append(pill);
    }

    return element;
}

function rewiresFor(pointId) {
    return data.rewires
        .filter((rewire) => rewire.from === pointId || rewire.to === pointId)
        .map((rewire) => ({
            id: rewire.from === pointId ? rewire.to : rewire.from,
            role: "rewire",
            label: `${rewire.from}.${rewire.field} -> ${rewire.to}`,
        }));
}

function globalX(point) {
    return point.roomX * data.room.width + point.x + 0.5;
}

function globalY(point) {
    return point.roomY * data.room.height + point.y + 0.5;
}

function pointRadius(point) {
    if (point.type === "exit") {
        return 0.85;
    }
    if (point.type === "jump_left" || point.type === "jump_right" || point.type === "wait") {
        return 0.72;
    }
    return 0.62;
}

function roomId(point) {
    return `${point.roomX},${point.roomY}`;
}

function shortPointName(id) {
    return id.replace(/^route_/, "");
}

function svgElement(name, attributes) {
    const element = document.createElementNS("http://www.w3.org/2000/svg", name);
    for (const [key, value] of Object.entries(attributes)) {
        element.setAttribute(key, String(value));
    }
    return element;
}
