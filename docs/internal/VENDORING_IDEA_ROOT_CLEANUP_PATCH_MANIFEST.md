# Vendoring IDEA Root Cleanup Patch Manifest

## Added / changed

- `.gitignore`
- `docs/internal/VENDORING_IDEA_ROOT_CLEANUP_AUDIT.md`
- `docs/internal/VENDORING_IDEA_ROOT_CLEANUP_PATCH_MANIFEST.md`

## Removed by apply script

- `.idea/Canonization.iml`
- `.idea/Vendor.iml`
- `.idea/codeception.xml`
- `.idea/commandlinetools/Symfony_3_26_26__6_19_PM.xml`
- `.idea/commandlinetools/schemas/frameworkDescriptionVersion1.1.4.xsd`
- `.idea/inspectionProfiles/Project_Default.xml`
- `.idea/inspectionProfiles/profiles_settings.xml`
- `.idea/jsonCatalog.xml`
- `.idea/laravel-idea.xml`
- `.idea/markdown.xml`
- `.idea/misc.xml`
- `.idea/modules.xml`
- `.idea/php.xml`
- `.idea/phpspec.xml`
- `.idea/phpunit.xml`
- `.idea/sqldialects.xml`
- `.idea/symfony2.xml`
- `.idea/vcs.xml`
- `.idea/workspace.xml`
- `.idea/` if empty after file retirement

## Notes

The patch does not delete arbitrary root content. Only the listed `.idea` files are retired, then empty `.idea` folders are removed.
