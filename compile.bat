@echo off
setlocal
pushd "%~dp0"

php tools\map-viewer\generate_asm.php --project
if errorlevel 1 goto :fail
php pack_rooms.php
if errorlevel 1 goto :fail
php gs\prepare.php
if errorlevel 1 goto :fail

if not exist release mkdir release
call :clean_release

_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_tap main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_tap -Dlanguage_en main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_tap -Dlanguage_cs main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_tap -Dlanguage_pl main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_tap -Dlanguage_es main.a80
if errorlevel 1 goto :fail

php tools\patch_tap_loader.php release\vsjo-kubikami-ru.tap release\vsjo-kubikami-en.tap release\vsjo-kubikami-cs.tap release\vsjo-kubikami-pl.tap release\vsjo-kubikami-es.tap
if errorlevel 1 goto :fail

_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_trd main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_trd -Dlanguage_en main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_trd -Dlanguage_cs main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_trd -Dlanguage_pl main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_trd -Dlanguage_es main.a80
if errorlevel 1 goto :fail

_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_gs_trd main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_gs_trd -Dlanguage_en main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_gs_trd -Dlanguage_cs main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_gs_trd -Dlanguage_pl main.a80
if errorlevel 1 goto :fail
_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_gs_trd -Dlanguage_es main.a80
if errorlevel 1 goto :fail

for %%F in (
    release\vsjo-kubikami-ru.tap
    release\vsjo-kubikami-en.tap
    release\vsjo-kubikami-cs.tap
    release\vsjo-kubikami-pl.tap
    release\vsjo-kubikami-es.tap
    release\vsjo-kubikami-ru.trd
    release\vsjo-kubikami-en.trd
    release\vsjo-kubikami-cs.trd
    release\vsjo-kubikami-pl.trd
    release\vsjo-kubikami-es.trd
    release\vsjo-kubikami-ru-gs.trd
    release\vsjo-kubikami-en-gs.trd
    release\vsjo-kubikami-cs-gs.trd
    release\vsjo-kubikami-pl-gs.trd
    release\vsjo-kubikami-es-gs.trd
) do if not exist "%%F" goto :fail

popd
exit /b 0

:clean_release
for %%F in (
    release\vsjo-kubikami-ru.tap
    release\vsjo-kubikami-en.tap
    release\vsjo-kubikami-cs.tap
    release\vsjo-kubikami-pl.tap
    release\vsjo-kubikami-es.tap
    release\vsjo-kubikami-ru.trd
    release\vsjo-kubikami-en.trd
    release\vsjo-kubikami-cs.trd
    release\vsjo-kubikami-pl.trd
    release\vsjo-kubikami-es.trd
    release\vsjo-kubikami-ru-gs.trd
    release\vsjo-kubikami-en-gs.trd
    release\vsjo-kubikami-cs-gs.trd
    release\vsjo-kubikami-pl-gs.trd
    release\vsjo-kubikami-es-gs.trd
    release\kubikami-ru.tap
    release\kubikami-en.tap
    release\kubikami-cs.tap
    release\kubikami-pl.tap
    release\kubikami-es.tap
    release\kubikami-ru.trd
    release\kubikami-en.trd
    release\kubikami-cs.trd
    release\kubikami-pl.trd
    release\kubikami-es.trd
    release\kubikami-ru-gs.trd
    release\kubikami-en-gs.trd
    release\kubikami-cs-gs.trd
    release\kubikami-pl-gs.trd
    release\kubikami-es-gs.trd
) do if exist "%%F" del /q "%%F"
exit /b 0

:fail
set "build_error=%errorlevel%"
call :clean_release
popd
if "%build_error%"=="0" set "build_error=1"
exit /b %build_error%
