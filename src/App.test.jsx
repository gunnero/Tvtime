// @vitest-environment jsdom
import "@testing-library/jest-dom/vitest";
import { cleanup, fireEvent, render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import { DetailModal } from "./App.jsx";

const movieItem = {
  id: "movie-watch-1",
  kind: "movie",
  movieId: 42,
  title: "Heat",
  meta: "170 min movie",
  progress: 100,
  badge: "watched",
};

const movieDetail = {
  id: 42,
  kind: "movie",
  movieId: 42,
  title: "Heat",
  subtitle: "Movie",
  meta: "170 min movie",
  status: "watched",
  watched: true,
  watchedCount: 1,
  rating: { id: 7, rating: 9 },
  notes: [{ id: 5, body: "Watch the diner scene again." }],
  watchHistory: [{ id: 11, watchedAt: "2026-07-01T12:00:00Z", runtime: 170, source: "manual" }],
  provider: { linked: true, linkedItemsCount: 1 },
};

function renderDetail(overrides = {}) {
  const props = {
    item: movieItem,
    detail: movieDetail,
    detailError: "",
    detailLoading: false,
    actionError: "",
    actionPending: false,
    onClose: vi.fn(),
    onSaveRating: vi.fn(),
    onClearRating: vi.fn(),
    onSaveNote: vi.fn(),
    onDeleteNote: vi.fn(),
    onMarkWatched: vi.fn(),
    onMarkUnwatched: vi.fn(),
    ...overrides,
  };

  const utils = render(<DetailModal {...props} />);

  return { ...props, ...utils };
}

afterEach(() => {
  cleanup();
});

describe("DetailModal", () => {
  it("renders media detail, rating, notes, history, and provider status", () => {
    renderDetail();

    expect(screen.getByRole("dialog", { name: /heat details/i })).toBeInTheDocument();
    expect(screen.getByText("Private note")).toBeInTheDocument();
    expect(screen.getByText("9/10")).toBeInTheDocument();
    expect(screen.getByText("Linked to 1 provider item")).toBeInTheDocument();
    expect(screen.getByText(/manual/i)).toBeInTheDocument();
  });

  it("saves and clears rating selections", () => {
    const props = renderDetail();

    fireEvent.click(screen.getByRole("button", { name: "8" }));
    expect(props.onSaveRating).toHaveBeenCalledWith(movieDetail, 8);

    fireEvent.click(screen.getByRole("button", { name: /clear rating/i }));
    expect(props.onClearRating).toHaveBeenCalledWith(movieDetail);
  });

  it("saves and deletes private notes", () => {
    const props = renderDetail();
    const note = screen.getByLabelText(/private note/i);

    fireEvent.change(note, { target: { value: "Updated note." } });
    fireEvent.click(screen.getByRole("button", { name: /save note/i }));

    expect(props.onSaveNote).toHaveBeenCalledWith(
      movieDetail,
      "Updated note.",
      movieDetail.notes[0],
    );

    fireEvent.click(screen.getByRole("button", { name: /delete note/i }));
    expect(props.onDeleteNote).toHaveBeenCalledWith(movieDetail, movieDetail.notes[0]);
  });

  it("triggers manual watched and unwatched actions", () => {
    const unwatchedDetail = { ...movieDetail, watched: false, watchHistory: [] };
    const unwatchedProps = renderDetail({ detail: unwatchedDetail });

    fireEvent.click(screen.getByRole("button", { name: /mark watched/i }));
    expect(unwatchedProps.onMarkWatched).toHaveBeenCalledWith(unwatchedDetail);

    unwatchedProps.unmount();

    const watchedProps = renderDetail();
    fireEvent.click(screen.getByRole("button", { name: /mark unwatched/i }));
    expect(watchedProps.onMarkUnwatched).toHaveBeenCalledWith(movieDetail);
  });

  it("shows loading and safe error states", () => {
    renderDetail({
      detail: null,
      detailError: "Could not load details.",
      detailLoading: true,
      actionError: "Could not save change.",
    });

    expect(screen.getByText("Loading details...")).toBeInTheDocument();
    expect(screen.getByText("Could not load details.")).toBeInTheDocument();
    expect(screen.getByText("Could not save change.")).toBeInTheDocument();
  });
});
