# Testing
This extension ships with the following tests:

- Unit tests (`Test/Unit/`)
- Integration tests (`Test/Integration/`)
- Playwright tests (`Test/Playwright/`), using the test page `/avif/test/images` and the `Loki_FunctionalTests` module

The test page `/avif/test/images` returns a 404 outside of developer mode, unless the hidden setting `yireo_avif/test/enabled` is set to `1` (the Playwright tests set it themselves):
```bash
bin/magento config:set yireo_avif/test/enabled 1
```

All of them are run by GitHub Actions (`.github/workflows/`).

Tests that need real AVIF encoding are skipped when PHP GD has no AVIF support. All other tests replace the encoder with `Test/Integration/Stub/FakeGdEncoder`.
