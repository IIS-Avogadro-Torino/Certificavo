@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../endroid/quality/bin/unit-test
bash "%BIN_TARGET%" %*
