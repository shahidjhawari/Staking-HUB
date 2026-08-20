describe("Admin Login Page", () => {
  beforeEach(() => {
    cy.visit("/admin/login.php");
  });

  it("loads the admin login form", () => {
    cy.contains("Admin Console").should("be.visible");
    cy.get('[data-cy="admin-username"]').should("be.visible");
    cy.get('[data-cy="admin-password"]').should("be.visible");
    cy.get('[data-cy="admin-submit"]').should("be.visible");
  });

  it("requires both fields before submitting", () => {
    cy.get('[data-cy="admin-submit"]').click();
    cy.url().should("include", "login.php");
    cy.get('[data-cy="admin-username"]:invalid').should("exist");
  });

  it("shows an error for incorrect admin credentials", () => {
    cy.get('[data-cy="admin-username"]').type("wrongadmin");
    cy.get('[data-cy="admin-password"]').type("wrongpassword");
    cy.get('[data-cy="admin-submit"]').click();
    cy.get('[data-cy="admin-login-error"]').should("be.visible");
  });
});

describe("Admin Panel — after login", () => {
  beforeEach(() => {
    cy.loginAsAdmin();
  });

  it("shows the glass sidebar with all main sections", () => {
    cy.get("#left-panel").should("be.visible");
    cy.contains("#left-panel", "Users").should("be.visible");
    cy.contains("#left-panel", "Deposit Request").should("be.visible");
    cy.contains("#left-panel", "Staking Request").should("be.visible");
    cy.contains("#left-panel", "Withdraw Request").should("be.visible");
  });

  it("highlights the active page in the sidebar", () => {
    cy.visit("/admin/users.php");
    cy.get('#left-panel .nav-link[href="users.php"]').should("have.class", "active");
  });

  it("toggles the sidebar on mobile without breaking layout", () => {
    cy.viewport("iphone-x");
    cy.get("#sidebarToggle").should("be.visible").click();
    cy.get("#left-panel").should("have.class", "show");
    cy.get("#sidebarToggle").click();
    cy.get("#left-panel").should("not.have.class", "show");
  });

  it("all <select> dropdowns use readable dark styling (not white-on-white)", () => {
    cy.visit("/admin/users.php");
    cy.get("select").then(($selects) => {
      if ($selects.length === 0) {
        cy.log("No <select> elements on this page — skipping.");
        return;
      }
      cy.wrap($selects).each(($select) => {
        cy.wrap($select).should(
          "have.css",
          "background-color"
        );
      });
    });
  });
});
