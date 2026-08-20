describe("User Dashboard", () => {
  beforeEach(() => {
    cy.loginAsUser();
  });

  it("shows the wallet balance card with a numeric value", () => {
    cy.get('[data-cy="wallet-balance"]')
      .should("be.visible")
      .invoke("text")
      .should("match", /^\$[\d,]+\.\d{2}$/);
  });

  it("shows the full navbar for a logged-in user", () => {
    cy.get('[data-cy="nav-profile"]').should("be.visible");
    cy.get('[data-cy="nav-wallet"]').should("be.visible");
    cy.get('[data-cy="nav-logout"]').should("be.visible");
  });

  it("logs the user out via the navbar link", () => {
    cy.get('[data-cy="nav-logout"]').click();
    cy.url().should("include", "index.php");
  });

  it("navigates to the profile page", () => {
    cy.get('[data-cy="nav-profile"]').click();
    cy.url().should("include", "profile.php");
  });

  it("navigates to Daily Earning page", () => {
    cy.get('[data-cy="nav-daily-earning"]').click();
    cy.url().should("include", "stacking_dummy.php");
  });
});
