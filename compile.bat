php pack_rooms.php
if errorlevel 1 exit /b %errorlevel%
_sjasmplus\sjasmplus.exe main.a80
if errorlevel 1 exit /b %errorlevel%
qsave1.sna
