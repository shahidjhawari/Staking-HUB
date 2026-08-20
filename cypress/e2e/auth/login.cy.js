describe("Login Page", () => {
  beforeEach(() => {
    cy.visit("/index.php");
  });

  it("loads the login page with all key elements visible", () => {
    cy.contains("Welcome back").should("be.visible");
    cy.get('[data-cy="login-email"]').should("be.visible");
    cy.get('[data-cy="login-password"]').should("be.visible");
    cy.get('[data-cy="login-submit"]').should("be.visible").and("contain", "Log In");
    cy.contains("a", "Sign up").should("have.attr", "href", "signup.php");
    cy.contains("a", "Forgot password?").should("have.attr", "href", "forgot_password.php");
  });

  it("requires both email and password before submitting (HTML5 validation)", () => {
    cy.get('[data-cy="login-submit"]').click();
    // Browser-native "required" validation blocks submission, so we should
    // still be on the login page.
    cy.url().should("include", "index.php");
    cy.get('[data-cy="login-email"]:invalid').should("exist");
  });

  it("shows a server-side error for an invalid email format", () => {
    cy.get('[data-cy="login-email"]').type("notarealemail");
    cy.get('[data-cy="login-password"]').type("somePassword123");
    cy.get('[data-cy="login-submit"]').click();
    // Native email input type blocks obviously malformed input client-side.
    cy.get('[data-cy="login-email"]:invalid').should("exist");
  });

  it("shows an error message for valid-format but incorrect credentials", () => {
    cy.get('[data-cy="login-email"]').type("doesnotexist@example.com");
    cy.get('[data-cy="login-password"]').type("wrongPassword123");
    cy.get('[data-cy="login-submit"]').click();
    cy.get('[data-cy="login-error"]').should("be.visible");
  });

  it("toggles password visibility when the eye icon is clicked", () => {
    cy.get('[data-cy="login-password"]').should("have.attr", "type", "password");
    cy.get(".toggle-password").click();
    cy.get('[data-cy="login-password"]').should("have.attr", "type", "text");
  });

  it("navigates to the signup page", () => {
    cy.contains("a", "Sign up").click();
    cy.url().should("include", "signup.php");
  });

  it("navigates to the forgot password page", () => {
    cy.contains("a", "Forgot password?").click();
    cy.url().should("include", "forgot_password.php");
  });
});
