describe("Signup Page", () => {
  beforeEach(() => {
    cy.visit("/signup.php");
  });

  it("loads the signup form with all fields", () => {
    cy.contains("Create your account").should("be.visible");
    cy.get('[data-cy="signup-name"]').should("be.visible");
    cy.get('[data-cy="signup-username"]').should("be.visible");
    cy.get('[data-cy="signup-email"]').should("be.visible");
    cy.get('[data-cy="signup-password"]').should("be.visible");
    cy.get('[data-cy="signup-confirm-password"]').should("be.visible");
    cy.get('[data-cy="signup-referral"]').should("be.visible");
    cy.get('[data-cy="signup-submit"]').should("be.visible");
  });

  it("enforces the username min length (8 characters)", () => {
    cy.get('[data-cy="signup-username"]').type("abc");
    cy.get('[data-cy="signup-username"]').then(($el) => {
      expect($el[0].validationMessage).to.not.be.empty;
    });
  });

  it("enforces the password min length (8 characters)", () => {
    cy.get('[data-cy="signup-password"]').type("123");
    cy.get('[data-cy="signup-password"]').then(($el) => {
      expect($el[0].checkValidity()).to.be.false;
    });
  });

  it("toggles visibility independently for password and confirm password", () => {
    cy.get('[data-cy="signup-password"]').should("have.attr", "type", "password");
    cy.get('[data-cy="signup-confirm-password"]').should("have.attr", "type", "password");

    cy.get('.toggle-password[data-target="password"]').click();
    cy.get('[data-cy="signup-password"]').should("have.attr", "type", "text");
    cy.get('[data-cy="signup-confirm-password"]').should("have.attr", "type", "password");

    cy.get('.toggle-password[data-target="confirmPassword"]').click();
    cy.get('[data-cy="signup-confirm-password"]').should("have.attr", "type", "text");
  });

  it("fills out the form with valid data and submits", () => {
    cy.fixture("users").then(({ validUser }) => {
      // Use a unique email/username each run so re-running tests doesn't
      // fail on "already registered" server-side checks.
      const unique = Date.now();
      cy.get('[data-cy="signup-name"]').type(validUser.name);
      cy.get('[data-cy="signup-username"]').type(`cyuser${unique}`);
      cy.get('[data-cy="signup-email"]').type(`cypress${unique}@example.com`);
      cy.get('[data-cy="signup-password"]').type(validUser.password);
      cy.get('[data-cy="signup-confirm-password"]').type(validUser.confirmPassword);
      cy.get('[data-cy="signup-submit"]').click();

      // Depending on app logic this either redirects to an OTP/verification
      // step or shows a success message — assert we've left the plain form.
      cy.url().should("not.eq", Cypress.config().baseUrl + "/signup.php");
    });
  });

  it("navigates back to the login page", () => {
    cy.contains("a", "Login").click();
    cy.url().should("include", "index.php");
  });
});
