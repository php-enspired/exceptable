# Documentation and The Wiki

The contents of `docs/` are also used to populate the wiki (or, depending on where you're reading this, this wiki is populated from the project `docs/`). To keep the wiki and docs in sync, you need to remember two things:

1) **Never** edit the wiki on GitHub.

2) **Always** make changes to `docs/` in a separate PR (never together with code changes).

3) **Always** use the `composer wiki:update` command to push `docs/` changes to the wiki.

For more details, see [GitHub Wiki Subtree Storage](https://gist.github.com/joshuajabbour/8569364).
