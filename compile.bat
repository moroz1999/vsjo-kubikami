@echo off
setlocal
pushd "%~dp0"

php tools\map-viewer\generate_asm.php --project
if errorlevel 1 goto :fail
php pack_rooms.php
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

php tools\patch_tap_loader.php release\kubikami-ru.tap release\kubikami-en.tap release\kubikami-cs.tap release\kubikami-pl.tap release\kubikami-es.tap
if errorlevel 1 goto :fail

_sjasmplus\sjasmplus.exe --nologo --outprefix=release/ -Drelease_trd main.a80
if errorlevel 1 goto :fail

for %%F in (
    release\kubikami-ru.tap
    release\kubikami-en.tap
    release\kubikami-cs.tap
    release\kubikami-pl.tap
    release\kubikami-es.tap
    release\kubikami-ru.trd
) do if not exist "%%F" goto :fail

popd
exit /b 0

:clean_release
for %%F in (
    release\kubikami-ru.tap
    release\kubikami-en.tap
    release\kubikami-cs.tap
    release\kubikami-pl.tap
    release\kubikami-es.tap
    release\kubikami-ru.trd
) do if exist "%%F" del /q "%%F"
exit /b 0

:fail
set "build_error=%errorlevel%"
call :clean_release
popd
if "%build_error%"=="0" set "build_error=1"
exit /b %build_error%
