@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../endroid/quality/bin/check
bash "%BIN_TARGET%" %*
