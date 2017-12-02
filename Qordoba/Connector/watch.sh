#!/usr/bin/env bash

fswatch -o ./ | xargs -n1 -I{} rsync -avzhp --executability ./ rsync://0.0.0.0/volume/app/code/Qordoba/Connector