#!/usr/bin/env bash
# =============================================================================
#  Eyeshot Tourism — Full Deploy: Local DDEV → Live Server
#  Run from your Mac:  bash deploy-local-to-live.sh
# =============================================================================
#
#  What this script does:
#  1. Transfers files  (theme, mu-plugins, plugins, uploads) via rsync over SSH
#  2. Transfers the DB deploy script and homepage XML to the live server
#  3. SSHes in and runs WP-CLI to apply all DB changes
#  4. SSHes in and imports the homepage XML
#  5. Cleans up temporary files from the live server
#
# =============================================================================

set -euo pipefail

# ── Configuration ─────────────────────────────────────────────────────────────
SSH_USER="eyesrzcn"
SSH_HOST="business34.web-hosting.com"
SSH_PORT="21098"
REMOTE_WP="/home/eyesrzcn/public_html"       # WordPress root on live server
LOCAL_WP="/Users/muneebmukhthar/Projects/Eyeshottourism"

SSH_CMD="ssh -p ${SSH_PORT} ${SSH_USER}@${SSH_HOST}"
RSYNC_SSH="rsync -az --progress -e 'ssh -p ${SSH_PORT}'"

# Colour helpers
GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; NC='\033[0m'
info()    { echo -e "${GREEN}✔ $1${NC}"; }
warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
error()   { echo -e "${RED}✗ $1${NC}"; exit 1; }
step()    { echo -e "\n${YELLOW}▶ $1${NC}"; }

# ── Preflight checks ──────────────────────────────────────────────────────────
step "Preflight checks"

# Check SSH connectivity
if ! ${SSH_CMD} -o ConnectTimeout=10 "echo connected" &>/dev/null; then
    error "Cannot connect to ${SSH_USER}@${SSH_HOST}:${SSH_PORT}. Check SSH key is added."
fi
info "SSH connection OK"

# Check WP-CLI is available on the server
if ! ${SSH_CMD} "which wp" &>/dev/null; then
    warning "WP-CLI not found at 'wp'. Trying common paths..."
    # Namecheap sometimes has it at /usr/local/bin/wp or ~/bin/wp
    WP_CLI=$(${SSH_CMD} "ls /usr/local/bin/wp ~/bin/wp 2>/dev/null | head -1" || echo "")
    if [ -z "$WP_CLI" ]; then
        error "WP-CLI not found on the server. Contact Namecheap support to enable it."
    fi
    info "Found WP-CLI at: $WP_CLI"
else
    WP_CLI="wp"
fi
info "WP-CLI available: $WP_CLI"

# Check local files exist
[ -f "${LOCAL_WP}/deploy-to-production.php" ]   || error "deploy-to-production.php not found locally"
[ -f "${LOCAL_WP}/homepage-export.xml" ]         || error "homepage-export.xml not found locally"
[ -d "${LOCAL_WP}/wp-content/themes/astra" ]     || error "Astra theme not found locally"
[ -f "${LOCAL_WP}/wp-content/mu-plugins/eyeshot-custom.php" ] || error "mu-plugin not found locally"
[ -f "${LOCAL_WP}/wp-content/mu-plugins/modern-cart-loader.php" ] || error "modern-cart-loader.php not found locally"
[ -d "${LOCAL_WP}/wp-content/plugins/modern-cart" ]               || error "modern-cart plugin directory not found locally"
[ -d "${LOCAL_WP}/wp-content/plugins/super-block-slider" ]        || error "super-block-slider plugin directory not found locally"

info "All local files present"

# ── Step 1: Transfer mu-plugins ───────────────────────────────────────────────
step "Step 1/8 — Uploading mu-plugins (custom CSS, loader & Modern Cart loader)"
rsync -az --progress \
    -e "ssh -p ${SSH_PORT}" \
    "${LOCAL_WP}/wp-content/mu-plugins/eyeshot-custom.php" \
    "${LOCAL_WP}/wp-content/mu-plugins/eyeshot-custom/" \
    "${LOCAL_WP}/wp-content/mu-plugins/modern-cart-loader.php" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_WP}/wp-content/mu-plugins/"
info "mu-plugins uploaded"

# ── Step 2: Transfer Astra theme ─────────────────────────────────────────────
step "Step 2/8 — Uploading Astra theme (~may take a minute)"
rsync -az --progress \
    -e "ssh -p ${SSH_PORT}" \
    "${LOCAL_WP}/wp-content/themes/astra/" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_WP}/wp-content/themes/astra/"
info "Astra theme uploaded"

# ── Step 3: Transfer MyFatoorah plugin ───────────────────────────────────────
step "Step 3/8 — Uploading MyFatoorah plugin"
rsync -az --progress \
    -e "ssh -p ${SSH_PORT}" \
    "${LOCAL_WP}/wp-content/plugins/myfatoorah-woocommerce/" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_WP}/wp-content/plugins/myfatoorah-woocommerce/"
info "MyFatoorah plugin uploaded"

# ── Step 4: Transfer Modern Cart plugin ──────────────────────────────────────
step "Step 4/8 — Uploading Modern Cart plugin (CartFlows v1.0.8)"
rsync -az --progress \
    -e "ssh -p ${SSH_PORT}" \
    "${LOCAL_WP}/wp-content/plugins/modern-cart/" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_WP}/wp-content/plugins/modern-cart/"
info "Modern Cart plugin uploaded"

# ── Step 5: Transfer Super Block Slider plugin ────────────────────────────────
step "Step 5/8 — Uploading Super Block Slider plugin"
rsync -az --progress \
    -e "ssh -p ${SSH_PORT}" \
    "${LOCAL_WP}/wp-content/plugins/super-block-slider/" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_WP}/wp-content/plugins/super-block-slider/"
info "Super Block Slider plugin uploaded"

# ── Step 6: Transfer uploads (new/changed files only, skip unchanged) ─────────
step "Step 6/8 — Syncing uploads folder (skip files already on server)"
warning "This may take several minutes for the first run (115 MB). Subsequent runs are fast."
rsync -az --progress --ignore-existing \
    -e "ssh -p ${SSH_PORT}" \
    "${LOCAL_WP}/wp-content/uploads/" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_WP}/wp-content/uploads/"
info "Uploads synced"

# ── Step 6: Upload deploy scripts ────────────────────────────────────────────
step "Step 7/8 — Uploading deploy scripts to live server"
rsync -az \
    -e "ssh -p ${SSH_PORT}" \
    "${LOCAL_WP}/deploy-to-production.php" \
    "${LOCAL_WP}/homepage-export.xml" \
    "${SSH_USER}@${SSH_HOST}:${REMOTE_WP}/"
info "Deploy scripts uploaded"

# ── Step 7: Run DB changes on live server via WP-CLI ─────────────────────────
step "Step 8/8 — Running DB deploy on live server"

echo ""
echo "--- Running deploy-to-production.php ---"
${SSH_CMD} "cd ${REMOTE_WP} && ${WP_CLI} eval-file deploy-to-production.php --url=https://eyeshottourism.com"

echo ""
echo "--- Importing homepage XML ---"
${SSH_CMD} "cd ${REMOTE_WP} && ${WP_CLI} import homepage-export.xml --authors=skip --url=https://eyeshottourism.com"

# ── Cleanup: remove temp files from live server ───────────────────────────────
echo ""
step "Cleanup — removing temporary files from live server"
${SSH_CMD} "rm -f ${REMOTE_WP}/deploy-to-production.php ${REMOTE_WP}/homepage-export.xml"
info "Temporary files removed from server"

# ── Flush caches ─────────────────────────────────────────────────────────────
echo ""
step "Flushing WordPress cache on live server"
${SSH_CMD} "cd ${REMOTE_WP} && ${WP_CLI} cache flush --url=https://eyeshottourism.com" || true
${SSH_CMD} "cd ${REMOTE_WP} && ${WP_CLI} rewrite flush --url=https://eyeshottourism.com" || true
${SSH_CMD} "cd ${REMOTE_WP} && ${WP_CLI} litespeed-purge all --url=https://eyeshottourism.com" || true
info "Cache flushed (object + rewrite + LiteSpeed)"

# ── Done ─────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}=============================================${NC}"
echo -e "${GREEN}  ✅  Deploy complete!${NC}"
echo -e "${GREEN}=============================================${NC}"
echo ""
echo "Next steps:"
echo "  1. Visit https://eyeshottourism.com and verify the site"
echo "  2. Check the homepage, navigation, and footer"
echo "  3. Go to WooCommerce → Settings → Payments → MyFatoorah"
echo "     and enter your LIVE API key"
echo "  4. Make a test purchase to confirm payments work"
echo ""
