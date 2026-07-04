export class SessionExpiredError extends Error {
  constructor(message = "Session expired") {
    super(message);
    this.name = "SessionExpiredError";
  }
}

export class ApiError extends Error {
  constructor(message = "API request failed", status = 500) {
    super(message);
    this.name = "ApiError";
    this.status = status;
  }
}

function readCookie(name) {
  if (typeof document === "undefined") {
    return "";
  }

  return document.cookie
    .split("; ")
    .find((entry) => entry.startsWith(`${name}=`))
    ?.split("=")[1] || "";
}

async function ensureCsrfCookie() {
  if (readCookie("XSRF-TOKEN")) {
    return;
  }

  await fetch("/api/v1/status", {
    credentials: "include",
    headers: { Accept: "application/json" },
  });
}

export async function apiRequest(path, options = {}) {
  const hasBody = Object.prototype.hasOwnProperty.call(options, "body");
  const method = (options.method || "GET").toUpperCase();

  if (!["GET", "HEAD", "OPTIONS"].includes(method)) {
    await ensureCsrfCookie();
  }

  const csrfToken = decodeURIComponent(readCookie("XSRF-TOKEN"));

  const response = await fetch(path, {
    ...options,
    credentials: "include",
    headers: {
      Accept: "application/json",
      ...(hasBody ? { "Content-Type": "application/json" } : {}),
      ...(csrfToken ? { "X-XSRF-TOKEN": csrfToken } : {}),
      ...(options.headers || {}),
    },
    body:
      hasBody && options.body !== undefined && typeof options.body !== "string"
        ? JSON.stringify(options.body)
        : options.body,
  });

  const contentType = response.headers.get("content-type") || "";
  const payload = contentType.includes("application/json")
    ? await response.json()
    : null;

  if (response.status === 401) {
    throw new SessionExpiredError(payload?.message);
  }

  if (!response.ok) {
    throw new ApiError(payload?.message || "API request failed", response.status);
  }

  return payload;
}
