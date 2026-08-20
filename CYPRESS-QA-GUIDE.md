# StakingHUB — Cypress QA Testing Guide

This guide walks you through everything, step by step, to get hands-on
Cypress/QA experience using your own StakingHUB project as the test target.

---

## 1. What's already set up for you

- `cypress.config.js` — Cypress configuration (base URL, viewport, etc.)
- `package.json` — npm scripts to run the tests
- `cypress/e2e/` — all the test files (specs), organized by feature:
  - `auth/login.cy.js` — login page tests
  - `auth/signup.cy.js` — signup page tests
  - `navigation/navbar.cy.js` — navbar + the overflow/logout bug we fixed
  - `responsive/viewports.cy.js` — mobile/tablet/desktop layout checks
  - `admin/admin_login.cy.js` — admin login + admin panel sidebar tests
  - `dashboard.cy.js` — logged-in user dashboard tests
- `cypress/support/commands.js` — reusable custom commands (`cy.loginAsUser()`, `cy.loginAsAdmin()`)
- `cypress/fixtures/users.json` — sample test data
- `data-cy="..."` attributes added to key elements (login, signup, navbar, admin login, dashboard) — this is the QA industry-standard way to write stable selectors that don't break when you change CSS classes or text.

You don't have to build this from scratch — but you should **read every
spec file** and understand what each test does. That's where the real
learning happens.

---

## 2. Prerequisites

1. **XAMPP/WAMP/Local server** running your PHP project + MySQL, exactly
   like you already have it for the site itself.
   - Your `connection.inc.php` expects the project folder to be reachable
     at `http://localhost/nawab/`. Either place this project in
     `htdocs/nawab`, or edit `baseUrl` in `cypress.config.js` to match
     wherever you actually run it (e.g. `http://localhost/coin`).
2. **Node.js** installed (v18 or newer). Check with:
   ```bash
   node -v
   npm -v
   ```
   If not installed, download from https://nodejs.org (LTS version).

---

## 3. Install Cypress

Open a terminal **in the project folder** (where `package.json` is) and run:

```bash
npm install
```

This downloads Cypress and its dependencies into `node_modules/` (this
folder is git-ignored — never commit it).

---

## 4. Set up test credentials

The tests need a **real user account** and a **real admin account** to log
in with (Cypress drives an actual browser against your actual app — it's
not mocking anything).

1. Manually sign up a test user on your site (e.g. through `signup.php`
   in your browser) — or use the `signup.cy.js` test itself to create one.
2. Copy the example env file:
   ```bash
   cp cypress.env.json.example cypress.env.json
   ```
3. Open `cypress.env.json` and fill in real values:
   ```json
   {
     "userEmail": "your-test-user@example.com",
     "userPassword": "YourTestPassword123",
     "adminUsername": "your-admin-username",
     "adminPassword": "YourAdminPassword123"
   }
   ```
   This file is git-ignored, so your real credentials never get committed.

---

## 5. Run Cypress for the first time

**Interactive mode (recommended while learning):**
```bash
npm run cy:open
```
This opens the Cypress Test Runner UI. Choose "E2E Testing" → pick a
browser (Chrome recommended) → click any spec file to watch it run
step-by-step in a real browser. You can see each command highlighted as
it executes — this is the best way to learn how Cypress "thinks."

**Headless mode (like a CI pipeline would run it):**
```bash
npm run cy:run
```
This runs every spec in the terminal without opening a visible browser,
prints pass/fail results, and takes screenshots automatically if
anything fails (`cypress/screenshots/`).

**Run just one folder of tests:**
```bash
npm run cy:run:auth
npm run cy:run:admin
```

---

## 6. How to read a test (using `login.cy.js` as an example)

```js
it("shows an error message for valid-format but incorrect credentials", () => {
  cy.get('[data-cy="login-email"]').type("doesnotexist@example.com");
  cy.get('[data-cy="login-password"]').type("wrongPassword123");
  cy.get('[data-cy="login-submit"]').click();
  cy.get('[data-cy="login-error"]').should("be.visible");
});
```

Read it like a sentence: *find the email field, type into it, find the
password field, type into it, click submit, then the error box should be
visible.* Every Cypress test follows this same **Arrange → Act → Assert**
pattern. Once you can read one test, you can read all of them.

---

## 7. Suggested learning path (do these in order)

1. **Run the existing suite** (`npm run cy:open`) and watch every spec pass.
   If something fails, that's normal — debug it (see Troubleshooting below).
2. **Break something on purpose** — e.g. remove the `required` attribute
   from the login email field — and re-run the login tests. Watch them
   fail. This teaches you what a test failure actually looks like and
   *why* tests matter.
3. **Write one new test yourself.** Good starter task: test the
   `forgot_password.php` OTP flow, or test that the `deposit.php` page
   loads correctly for a logged-in user.
4. **Add assertions to an existing test** — e.g. in `dashboard.cy.js`,
   add a check that the navbar wallet icon links to `dashboard.php`.
5. **Try a negative test** — deliberately submit bad data (SQL-injection-like
   strings, empty fields, huge numbers in the deposit amount) and confirm
   the app handles it gracefully instead of breaking. This is real QA
   thinking: testing what *shouldn't* work, not just what should.
6. **Learn `cy.intercept()`** — once comfortable, look up how to spy on
   or stub network requests. Not needed for this PHP app's basic flows,
   but it's a core Cypress skill used constantly in real jobs.
7. **Set up a GitHub Actions workflow** to run `npm run cy:run` on every
   push (optional, but great for a portfolio/resume line: "set up CI for
   automated E2E testing").

---

## 8. Adding `data-cy` to more pages

Right now, `data-cy` attributes exist on: login, signup, navbar, admin
login, admin sidebar, and the dashboard balance card. As you write more
tests for other pages (staking, deposit, profile, team building, etc.),
follow the same pattern:

```php
<input type="text" class="form-control" data-cy="deposit-amount" ...>
<button class="btn btn-primary" data-cy="deposit-submit">Submit</button>
```

Then in your spec:
```js
cy.get('[data-cy="deposit-amount"]').type("100");
cy.get('[data-cy="deposit-submit"]').click();
```

**Why `data-cy` instead of classes/IDs?** Classes and IDs change when
designers restyle a page (like we just did!). A dedicated `data-cy`
attribute is only ever used by tests, so your tests don't break just
because the CSS class name changed.

---

## 9. Troubleshooting

| Problem | Likely fix |
|---|---|
| `cy.visit()` fails / blank page | Confirm XAMPP/Apache + MySQL are running and the URL in `cypress.config.js` matches your local site URL. |
| Login tests fail immediately | Double-check `cypress.env.json` has a real, currently-working user account. |
| Admin tests fail | Same — confirm the admin account credentials are correct and the admin account isn't locked. |
| `cy.session()` errors | Clear cookies/cache, or delete `cypress/` cache via `npx cypress cache clear` then `npm install` again. |
| Tests are flaky (pass sometimes, fail others) | Usually a timing issue — check if the page has a slow DB query; you can raise `defaultCommandTimeout` in `cypress.config.js`. |

---

## 10. What to put on your resume/LinkedIn after this

Once you've gone through this guide and written a few of your own tests,
you can genuinely say:

- "Wrote and maintained an E2E test suite using **Cypress** covering
  authentication, navigation, responsive layout, and admin workflows."
- "Used **data-testid/data-cy** selector strategy for resilient test
  automation."
- "Practiced both positive and negative test-case design."
- (If you do step 7) "Integrated automated E2E tests into a **CI/CD**
  pipeline with GitHub Actions."

Good luck — and go break things on purpose, that's the job. 🙂
