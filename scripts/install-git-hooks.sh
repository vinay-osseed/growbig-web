#!/usr/bin/env bash
set -euo pipefail

mkdir -p .git/hooks
cp scripts/git-hooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit

echo "Git pre-commit hook installed."
