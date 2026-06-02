# Deployment

This repository deploys the Skinny Moo Contract Builder plugin to WordPress with GitHub Actions.

## How It Works

On every push to `main`, `.github/workflows/deploy.yml` copies the plugin files to the live server over SSH using `rsync`.

This is simpler than making the live server run `git pull` because the public GitHub repo does not need to be authenticated from the live server.

## Local/GitHub Sync Workflow

Use `scripts/sync-with-origin.sh` from this plugin directory to keep this local checkout and GitHub's `main` branch in sync.

To pull the latest GitHub changes into the local plugin and verify both sides match:

```bash
scripts/sync-with-origin.sh
```

To commit local edits, rebase them on top of GitHub, push them, and verify both sides match:

```bash
scripts/sync-with-origin.sh -m "Describe the plugin change"
```

After a successful push to `main`, GitHub Actions automatically deploys the repository files to the live WordPress plugin directory. Check the repository's Actions tab if the live server does not reflect the pushed commit.

## Required GitHub Secrets

Add these in GitHub:

`Settings > Secrets and variables > Actions > Repository secrets`

- `DEPLOY_HOST` - live server hostname, for example `ssh.example.com`
- `DEPLOY_PORT` - SSH port, usually `22`
- `DEPLOY_USER` - SSH username
- `DEPLOY_SSH_KEY` - private key GitHub Actions will use to SSH into the server
- `DEPLOY_KNOWN_HOSTS` - output of `ssh-keyscan -p <port> <host>`
- `DEPLOY_PATH` - absolute live plugin path, ending in `skinny-moo-contract-builder-1`

Example `DEPLOY_PATH`:

```text
/home/customer/www/skinnymoo.com/public_html/wp-content/plugins/skinny-moo-contract-builder-1
```

## One-Time Server Setup

1. Create or choose an SSH keypair for deployment.
2. Add the public key to the live server user's `~/.ssh/authorized_keys`.
3. Add the private key to GitHub as `DEPLOY_SSH_KEY`.
4. Make sure `DEPLOY_PATH` exists and points to the live plugin directory.
5. Run the GitHub Actions workflow manually once from the Actions tab.

## Notes

- The deploy excludes `.git/`, `.github/`, `.gitignore`, `CLAUDE.md`, `DEPLOYMENT.md`, and `scripts/`.
- The workflow uses `--delete`, so files removed from the repository are removed from the live plugin directory on deploy.
- Keep WordPress uploads, generated files, backups, and credentials out of this plugin directory.
- `scripts/sync-with-origin.sh` intentionally refuses uncommitted local changes unless you provide `-m`, so accidental edits are not pushed silently.
