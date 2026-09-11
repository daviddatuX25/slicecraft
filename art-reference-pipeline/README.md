# SliceCraft Cake Image Reference Pipeline

Purpose: prepare original visual assets for the Page 3 custom cake builder without generating images inside this repository.

This folder is a working reference and prompt package for the teammate who will generate the images one by one.

## Folder map

- `source-inspiration/cake2go-classic-cakes/` — downloaded reference images from the supplied Cake2Go Classic Cakes page. Inspiration only; do not publish these images in the Laravel site.
- `standalone-additives/` — standalone topping, drizzle, decoration, fruit, and size references to gather or create later.
- `prompts/00-style-bible.md` — consistent visual direction for every generated image.
- `prompts/20-combination-prompts.md` — one prompt block per planned generated image.
- `prompts/filename-map.csv` — exact output filenames and combination metadata.
- `source-manifest.md` — reference URLs, source names, and usage boundaries.

## Important boundary

The Cake2Go images are external reference material. They are not final assets and must not be copied into `public/` or committed as customer-facing product images. The final SliceCraft images must be newly generated and original.

## Generation workflow

1. Review the style bible.
2. Review the relevant base reference image.
3. Attach the standalone additive references listed in the prompt, if available.
4. Generate one image only.
5. Save it using the exact filename in `filename-map.csv`.
6. Check that it is a clean product photograph, has no brand/logo/text, and matches the style bible.
7. Record the generation tool/model and date in the teammate's generation log.
8. Do not continue to the next image until the filename and visual quality have been checked.

No image generation has been performed by this preparation task.
