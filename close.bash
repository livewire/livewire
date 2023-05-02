#!/bin/bash

osascript -e 'tell application "System Events"
        tell process "Code"
            set frontWindow to the first window whose frontmost is true
            repeat with aWindow in (get every window)
                if aWindow is not frontWindow then
                    close aWindow
                end if
            end repeat
        end tell
    end tell'


# vscode_main_pid=$(pgrep -f ".*electron.*code.*" | head -n 1)
# vscode_renderer_pids=$(pgrep -f ".*electron.*code.*" | tail -n +2)

# echo "VS Code Main Process PID: ${vscode_main_pid}"
# echo "VS Code Renderer Processes PIDs:"

# for pid in ${vscode_renderer_pids}; do
#     echo "  - ${pid}"
# done
