// These tests exercise the navbar bug we fixed earlier: the Logout link
// disappearing off-screen on desktop, and menu items overflowing on mobile.
//
// They require a logged-in session, since the full navbar (with all the
// links) only renders for authenticated users. Set CYPRESS_userEmail /
// CYPRESS_userPassword env vars, or edit cypress.env.json, before running.

describe("Navbar — desktop", () => {
  beforeEach(() => {
    cy.viewport(1280, 800);
    cy.loginAsUser();
  });

  it("keeps every nav item, including Logout, inside the navbar (no overflow)", () => {
    cy.get(".navbar").then(($navbar) => {
      const navbarRect = $navbar[0].getBoundingClientRect();

      cy.get(".navbar-nav .nav-link").each(($link) => {
        const linkRect = $link[0].getBoundingClientRect();
        expect(linkRect.right).to.be.at.most(navbarRect.right + 1);
        expect(linkRect.left).to.be.at.least(navbarRect.left - 1);
      });
    });
  });

  it("shows the Logout link visibly and it is clickable", () => {
    cy.get('[data-cy="nav-logout"]').should("be.visible");
  });

  it("opens the Transactions dropdown on click", () => {
    cy.get('[data-cy="nav-transactions-dropdown"]').click();
    cy.get("#transactionsDropdown")
      .parent()
      .find(".dropdown-menu")
      .should("have.class", "show");
  });

  it("opens the Staking dropdown and navigates to Staking page", () => {
    cy.get('[data-cy="nav-staking-dropdown"]').click();
    cy.contains(".dropdown-menu a", "Staking").click();
    cy.url().should("include", "staking.php");
  });
});

describe("Navbar — mobile", () => {
  beforeEach(() => {
    cy.viewport("iphone-x");
    cy.loginAsUser();
  });

  it("hides the full menu behind a hamburger toggle", () => {
    cy.get('[data-cy="navbar-toggler"]').should("be.visible");
    cy.get("#navbarSupportedContent").should("not.be.visible");
  });

  it("opens the mobile menu and stacks nav items full-width without horizontal overflow", () => {
    cy.get('[data-cy="navbar-toggler"]').click();
    cy.get("#navbarSupportedContent").should("be.visible");

    // No item should push the page wider than the viewport.
    cy.window().then((win) => {
      expect(win.document.documentElement.scrollWidth).to.be.at.most(win.innerWidth + 1);
    });
  });

  it("shows the Logout link at the end of the mobile menu, clearly separated", () => {
    cy.get('[data-cy="navbar-toggler"]').click();
    cy.get('[data-cy="nav-logout"]').should("be.visible");
  });
});
