#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'USAGE'
Usage:
  scripts/sync-with-origin.sh
  scripts/sync-with-origin.sh -m "Commit message"

What it does:
  - Fetches origin/main.
  - If -m is supplied, commits all local changes with that message.
  - Rebases local main on origin/main.
  - Pushes local main to origin/main when local commits exist.
  - Verifies local HEAD and origin/main are the same commit.

Without -m, the working tree must be clean.
USAGE
}

commit_message=""
remote="${REMOTE:-origin}"
branch="${BRANCH:-main}"

while getopts ":m:h" opt; do
  case "$opt" in
    m) commit_message="$OPTARG" ;;
    h)
      usage
      exit 0
      ;;
    :)
      echo "Missing value for -$OPTARG" >&2
      usage >&2
      exit 2
      ;;
    \?)
      echo "Unknown option: -$OPTARG" >&2
      usage >&2
      exit 2
      ;;
  esac
done

repo_root="$(git rev-parse --show-toplevel)"
cd "$repo_root"

current_branch="$(git branch --show-current)"
if [[ "$current_branch" != "$branch" ]]; then
  echo "Expected branch '$branch', but current branch is '$current_branch'." >&2
  exit 1
fi

git fetch --prune "$remote"

if [[ -n "$(git status --porcelain)" ]]; then
  if [[ -z "$commit_message" ]]; then
    echo "Local changes exist. Re-run with -m \"Commit message\" to commit and sync them." >&2
    git status --short
    exit 1
  fi

  git add -A
  git commit -m "$commit_message"
fi

git rebase "$remote/$branch"

if ! git diff --quiet "$remote/$branch" HEAD; then
  git push "$remote" "$branch"
  git fetch --prune "$remote"
fi

local_head="$(git rev-parse HEAD)"
remote_head="$(git rev-parse "$remote/$branch")"

if [[ "$local_head" != "$remote_head" ]]; then
  echo "Sync failed: local HEAD ($local_head) does not match $remote/$branch ($remote_head)." >&2
  exit 1
fi

if [[ -n "$(git status --porcelain)" ]]; then
  echo "Sync failed: working tree is not clean." >&2
  git status --short
  exit 1
fi

echo "Synced $branch with $remote/$branch at $local_head."
