import { afterEach, describe, expect, it, vi } from "vitest";
import { apiRequest, SessionExpiredError } from "./api.js";

describe("apiRequest", () => {
  afterEach(() => {
    vi.unstubAllGlobals();
  });

  it("sends same-origin session cookies with JSON requests", async () => {
    const fetchMock = vi.fn().mockResolvedValue({
      ok: true,
      status: 200,
      headers: new Headers({ "content-type": "application/json" }),
      json: async () => ({ ok: true }),
    });
    vi.stubGlobal("fetch", fetchMock);

    await expect(apiRequest("/api/v1/dashboard")).resolves.toEqual({ ok: true });

    expect(fetchMock).toHaveBeenCalledWith(
      "/api/v1/dashboard",
      expect.objectContaining({
        credentials: "include",
        headers: expect.objectContaining({ Accept: "application/json" }),
      }),
    );
  });

  it("raises a session-expired error for unauthenticated API responses", async () => {
    vi.stubGlobal(
      "fetch",
      vi.fn().mockResolvedValue({
        ok: false,
        status: 401,
        headers: new Headers({ "content-type": "application/json" }),
        json: async () => ({ message: "Unauthenticated." }),
      }),
    );

    await expect(apiRequest("/api/v1/dashboard")).rejects.toBeInstanceOf(
      SessionExpiredError,
    );
  });
});
