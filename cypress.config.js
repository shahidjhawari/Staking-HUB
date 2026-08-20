const { defineConfig } = require("cypress");

module.exports = defineConfig({
  projectId: '1qbaky',
  e2e: {
    // Change this if your local project folder name / XAMPP path is different.
    // connection.inc.php currently expects the project to live at: htdocs/nawab
    baseUrl: "http://localhost/coin",

    setupNodeEvents(on, config) {
      // implement node event listeners here if needed later
      return config;
    },

    viewportWidth: 1280,
    viewportHeight: 800,
    defaultCommandTimeout: 8000,
    video: false,
    screenshotOnRunFailure: true,
  },
});
