/**
 * Logs a normal user in through the real UI form and keeps the resulting
 * session cached between tests (Cypress re-uses it instead of logging in
 * again for every single test, which makes the suite much faster).
 */
Cypress.Commands.add("loginAsUser", (email, password) => {
  const creds = {
    email: email || Cypress.env("userEmail"),
    password: password || Cypress.env("userPassword"),
  };

  cy.session(
    ["user-session", creds.email],
    () => {
      cy.visit("/index.php");
      cy.get('[data-cy="login-email"]').type(creds.email);
      cy.get('[data-cy="login-password"]').type(creds.password, { log: false });
      cy.get('[data-cy="login-submit"]').click();
      cy.url().should("include", "dashboard.php");
    },
    {
      validate() {
        cy.getCookie("PHPSESSID").should("exist");
      },
    }
  );

  cy.visit("/dashboard.php");
});

/**
 * Logs the admin in through the admin login form.
 */
Cypress.Commands.add("loginAsAdmin", (username, password) => {
  const creds = {
    username: username || Cypress.env("adminUsername"),
    password: password || Cypress.env("adminPassword"),
  };

  cy.session(["admin-session", creds.username], () => {
    cy.visit("/admin/login.php");
    cy.get('[data-cy="admin-username"]').type(creds.username);
    cy.get('[data-cy="admin-password"]').type(creds.password, { log: false });
    cy.get('[data-cy="admin-submit"]').click();
    cy.url().should("include", "/admin/");
  });

  cy.visit("/admin/index.php");
});
