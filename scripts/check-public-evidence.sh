#!/usr/bin/env bash
set -euo pipefail

tracked_files=()
while IFS= read -r -d '' file; do
  tracked_files+=("$file")
done < <(git ls-files -z)

content_files=()
for file in "${tracked_files[@]}"; do
  [[ "$file" == "scripts/check-public-evidence.sh" ]] || content_files+=("$file")
done

failures=0
scan() {
  local label="$1"
  local expression="$2"
  shift 2
  local matches
  matches=$(rg -n -i "$expression" "$@" 2>/dev/null || true)
  if [[ -n "$matches" ]]; then
    printf '%s\n%s\n' "$label" "$matches" >&2
    failures=1
  fi
}

scan "Private topology found" '(ccc\.razbudise\.mk|mediahub\.razbudise\.mk|web0[0-9]|/home/[A-Za-z0-9._-]+/|root@[A-Za-z0-9._-]+|^[[:space:]]*HostName[[:space:]]+)' "${content_files[@]}"
scan "Credential or private-key pattern found" '(-----BEGIN (RSA |EC |OPENSSH |DSA )?PRIVATE KEY-----|gh[pousr]_[A-Za-z0-9_]{20,}|AKIA[0-9A-Z]{16}|sk-[A-Za-z0-9]{20,}|xox[baprs]-[A-Za-z0-9-]{10,})' "${content_files[@]}"

private_artifacts=$(printf '%s\n' "${tracked_files[@]}" | rg -i '(^|/)(exports?|backups?)/|\.(sql|dump|sqlite|sqlite3|db|zip|tar|tgz|gz)$' || true)
if [[ -n "$private_artifacts" ]]; then
  printf 'Tracked private data or backup artifact found\n%s\n' "$private_artifacts" >&2
  failures=1
fi

if (( failures )); then
  exit 1
fi

printf 'Public-evidence scan passed.\n'
