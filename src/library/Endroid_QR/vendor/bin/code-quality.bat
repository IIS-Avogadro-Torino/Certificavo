@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../endroid/quality/bin/code-quality
bash "%BIN_TARGET%" %*
