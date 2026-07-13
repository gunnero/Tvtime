# MediaHub asset strategy

## Decision summary

MediaHub should keep only product-owned identity assets, reproducible synthetic placeholders, and reviewed screenshots in Git. Third-party movie or television artwork should be fetched at runtime under provider terms or replaced with neutral placeholders; it should not be redistributed casually in the repository.

## Audited asset groups

| Group | Source/rights | Reproducibility | Git decision |
| --- | --- | --- | --- |
| Eight `movie-poster-*.png` files | Named “generated,” but no prompt, manifest, license, or approval record | Not reproducible | Removed from current tree |
| `favicon.svg`, pinned-tab SVG, manifest icons | Project identity assets | Source-controlled SVG | Keep |
| Recruiter screenshots | Local UI with synthetic data; governed by screenshot policy | Re-capturable | Keep optimized PNGs |
| Runtime metadata artwork | External provider boundary | Provider-dependent | Do not commit |
| Private avatar and import caches | User-owned/private | Environment-local | Ignore and never commit |

## Findings

The removed poster PNGs were 1024×1536 RGB files, about 20 MB total, and contained no EXIF/IPTC/XMP metadata. Lack of embedded metadata is not proof of ownership. The frontend already treated their paths as invalid artwork and rendered neutral placeholders, so removal does not eliminate a supported product dependency.

Git LFS or release artifacts would reduce repository weight but would not solve rights or provenance. They are therefore not recommended for these assets.

## Approved future workflow

1. Prefer CSS/SVG neutral placeholders or clearly synthetic, project-owned artwork.
2. Store generation prompts, tool/version, date, approver, and hash in a manifest.
3. Optimize raster assets to an appropriate web format and size.
4. Strip metadata and visually inspect every artifact.
5. Record license and attribution when an external asset is intentionally used.
6. Keep runtime provider artwork out of Git.
