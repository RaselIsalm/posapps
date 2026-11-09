@echo off
REM ONETECH POS — Silent Auto Print (Chrome kiosk-printing)
REM 1) Set RONGTA 80mm as Windows default printer
REM 2) Double-click this file to open POS (Print dialog will be skipped)
setlocal
set "POS_URL=http://127.0.0.1:8000/quick-sale"
set "CHROME="

if exist "%ProgramFiles%\Google\Chrome\Application\chrome.exe" set "CHROME=%ProgramFiles%\Google\Chrome\Application\chrome.exe"
if not defined CHROME if exist "%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe" set "CHROME=%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"
if not defined CHROME if exist "%LocalAppData%\Google\Chrome\Application\chrome.exe" set "CHROME=%LocalAppData%\Google\Chrome\Application\chrome.exe"

if not defined CHROME (
  echo Chrome not found. Install Google Chrome, then run this again.
  pause
  exit /b 1
)

start "" "%CHROME%" --kiosk-printing --new-window "%POS_URL%"
endlocal
