// Checks the glass theme's core building blocks (auth card, buttons) don't
// break or overflow across common breakpoints.

const viewports = [
  { name: "iPhone SE", width: 375, height: 667 },
  { name: "iPhone 12", width: 390, height: 844 },
  { name: "iPad", width: 768, height: 1024 },
  { name: "Laptop", width: 1280, height: 800 },
  { name: "Desktop", width: 1920, height: 1080 },
];

describe("Login page — responsive layout", () => {
  viewports.forEach(({ name, width, height }) => {
    it(`renders the auth card correctly at ${name} (${width}x${height})`, () => {
      cy.viewport(width, height);
      cy.visit("/index.php");

      cy.get(".auth-card").should("be.visible");
      cy.get('[data-cy="login-email"]').should("be.visible");
      cy.get('[data-cy="login-submit"]').should("be.visible");

      // The card should never force horizontal scrolling.
      cy.window().then((win) => {
        expect(win.document.documentElement.scrollWidth).to.be.at.most(width + 1);
      });
    });
  });
});

describe("Signup page — responsive layout", () => {
  viewports.forEach(({ name, width, height }) => {
    it(`renders the signup card correctly at ${name} (${width}x${height})`, () => {
      cy.viewport(width, height);
      cy.visit("/signup.php");

      cy.get(".auth-card").should("be.visible");
      cy.get('[data-cy="signup-submit"]').should("be.visible");

      cy.window().then((win) => {
        expect(win.document.documentElement.scrollWidth).to.be.at.most(width + 1);
      });
    });
  });
});
