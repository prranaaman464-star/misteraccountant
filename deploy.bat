@echo off
REM Deploy to production - double-click ya "deploy" type karke chalao
cd /d "%~dp0"
powershell -ExecutionPolicy Bypass -File "%~dp0deploy.ps1" %*
pause
