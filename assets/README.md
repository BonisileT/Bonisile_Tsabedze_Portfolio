# Bonisile Tsabedze — IT Portfolio

A static portfolio website (HTML, CSS, vanilla JavaScript — no database, no backend).

| File | Purpose |
|---|---|
| `index.html` | The **public** website everyone sees |
| `edit.html` | The **private editor** you use to change the website |
| `data/content.js` | All website text/links/lists. Rewritten by the editor |
| `assets/evidence/` | CV, certificates, project evidence (PDFs/images) |
| `assets/uploads/` | Images uploaded through the editor |

Open `index.html` by double-clicking it to preview locally.

---

## 1. Publish on GitHub Pages (one time)

1. Sign in at <https://github.com> and click **New repository**.
2. Name it `YOUR-USERNAME.github.io` (this gives the address `https://YOUR-USERNAME.github.io`) — or any name, e.g. `portfolio` (address `https://YOUR-USERNAME.github.io/portfolio/`). Choose **Public**. Click **Create repository**.
3. Upload the files — pick one:
   - **Website:** on the new repo page click **uploading an existing file**, drag in the **contents** of this folder (so `index.html` is at the top level, not inside a sub-folder), then **Commit changes**. Make sure `.nojekyll` and the `data` and `assets` folders are included.
   - **Git:**
     ```bash
     cd Bonisile_Tsabedze_Portfolio
     git init -b main
     git add .
     git commit -m "Initial portfolio"
     git remote add origin https://github.com/YOUR-USERNAME/YOUR-REPO.git
     git push -u origin main
     ```
4. In the repo go to **Settings → Pages**. Under **Build and deployment** choose **Deploy from a branch**, branch **main**, folder **/ (root)**, then **Save**.
5. Wait 1–2 minutes. Your site is live at the address shown on that page.

## 2. Editing the website with `edit.html`

Open `https://YOUR-USERNAME.github.io/edit.html` (or double-click `edit.html` on your computer — both work).

- Use the tabs on the left: **Hero & CV, About, Education, Skills, Projects, Certificates, Achievements, Important info, Career goals, Contact details, Site & layout**.
- Every change shows instantly in the **live preview** on the right (**Preview** button on phones).
- Add projects, certificates, achievements and information boxes with the **+ Add** buttons. Reorder with ↑ ↓, delete with ✕.
- Upload images/PDFs with **Upload file…** (certificates, project screenshots, your CV).
- **Site & layout** lets you show/hide sections, change their order, the menu, accent colour and default theme.

### Connect the editor to GitHub (one time, ~2 minutes)

1. Open <https://github.com/settings/personal-access-tokens/new>.
2. **Repository access → Only select repositories →** choose your portfolio repo.
3. **Permissions → Repository permissions → Contents → Read and write.**
4. Set an expiry (e.g. 90 days) and click **Generate token**. Copy it (starts with `github_pat_`).
5. In `edit.html` open **Publish & backup**, enter your username, repository name, branch (`main`) and the token. Click **Test connection**.

### Publishing a change

Click **Publish**. The editor saves your changes (and any uploaded files) to GitHub in a single commit; GitHub Pages refreshes the live site in about a minute. Refresh the page to see it.

### Security notes

- `edit.html` is reachable by anyone who knows the address, **but nobody can change your site without your token**. The token is the only key — never share it.
- By default the token is forgotten when you close the tab. Tick *Remember the token on this device* only on your own computer.
- If you'd rather not publish `edit.html` at all, simply don't upload it (or delete it from the repo). You can still edit by opening `edit.html` from your computer: it publishes to GitHub the same way.
- Treat anything you publish as public — don't add ID numbers or anything private.

### No token? Manual method

In **Publish & backup** choose **Download content.js**, then in GitHub open `data/content.js` → **✎ Edit** (or upload and replace the file), paste/replace and commit. Upload any new images/PDFs to the same folder paths shown in the editor.

## Files and sizes

Images over 1.5 MB are shrunk automatically. Keep each file under 25 MB. Use short, simple file names.

## Troubleshooting

| Problem | Fix |
|---|---|
| "GitHub said 404" | Check the username/repository spelling and that the token was given access to **this** repository with *Contents: Read and write*. |
| "GitHub said 401" | Token is wrong or expired — generate a new one. |
| "repository looks empty" | Upload the website files first (step 3 above). |
| Site didn't change | Wait a minute, then hard-refresh (Ctrl+Shift+R). Check **Actions** tab in the repo for the Pages build. |
| Lost unsaved edits | The editor keeps a local draft; reopen `edit.html` and click **Restore them**. |
