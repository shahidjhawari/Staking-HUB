// This file runs before every spec file.
// Load custom commands defined in commands.js
import "./commands";

// PHP apps sometimes throw harmless JS warnings from third-party widgets
// (e.g. the Telegram floating button script). Don't fail tests because of
// uncaught exceptions that aren't related to what we're testing.
Cypress.on("uncaught:exception", () => {
  return false;
});
