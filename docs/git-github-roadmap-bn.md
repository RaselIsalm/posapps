# Git ও GitHub: Terminal দিয়ে শেখার Roadmap

এই গাইডটি বাংলা ব্যাখ্যা ও English technical terms একসাথে ব্যবহার করে লেখা। ধাপে ধাপে পড়ে PowerShell-এ command চালাও। Git-এর অসংখ্য advanced feature আছে; এখানে দৈনন্দিন development, backup, teamwork ও GitHub automation-এর দরকারি বিষয় সাজানো হয়েছে।

## 0. Git ও GitHub কী?

- **Git** হলো computer-এ চলা version-control tool; পরিবর্তনের ইতিহাস রাখে।
- **GitHub** হলো online hosting/collaboration service; Git repository, review, issue ও automation রাখে।
- **Repository (repo)**: project files এবং commit history।
- **Working tree**: বর্তমান files। **Staging area/index**: পরের commit-এ রাখার জন্য বাছাই করা পরিবর্তন।
- **Commit**: history-তে রাখা snapshot। **Branch**: আলাদা কাজের history line। **Remote**: online repo address/nickname; সাধারণত `origin`।

```text
Edit files → git add → git commit → git push → GitHub
GitHub changes → git fetch/pull → local computer
```

## 1. Terminal setup

```powershell
git --version
git config --global --list
git config --global user.name "Your Name"
git config --global user.email "you@example.com"
```

নাম ও email commit-এর author পরিচয়; এগুলো GitHub login নয়। এক project-এ আলাদা পরিচয় দিতে `--global` বাদ দিয়ে project folder-এ চালাও।

```powershell
Get-Location
Set-Location C:\Users\USER\Desktop\posapps-main
Get-ChildItem -Force
```

URL একা PowerShell-এ চালিও না—URL Git command-এর argument, যেমন `git remote add origin URL`।

## 2. নতুন local project প্রথমবার GitHub-এ তোলা

GitHub-এ **empty repository** তৈরি করে HTTPS URL কপি করো। README/license আগেই GitHub-এ বানালে history আলাদা হতে পারে। এরপর project folder-এ:

```powershell
git init
git status
git add .
git status
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/USERNAME/REPOSITORY.git
git remote -v
git push -u origin main
```

`USERNAME/REPOSITORY`-এর বদলে নিজের URL দাও। GitHub browser authentication চাইলে browser-এ authorize করো; password/token code বা chat-এ দিও না।

GitHub repo আগে থেকেই থাকলে নতুন করে `git init` না করে clone করা সাধারণত সহজ:

```powershell
Set-Location $HOME\Desktop
git clone https://github.com/USERNAME/REPOSITORY.git
Set-Location .\REPOSITORY
```

## 3. প্রতিদিনের routine

```powershell
git status
git pull --ff-only
# ফাইল edit করো
git diff
git add path\to\changed-file
git diff --staged
git commit -m "fix: describe the change"
git push
```

- `status`: branch, modified/untracked/staged files দেখায়।
- `diff`: unstaged edit; `diff --staged`: পরের commit-এ যাওয়া edit।
- `add`: commit-এর জন্য পরিবর্তন বেছে stage করে। `git add .` সবকিছু stage করে—তারপর `status` দেখো।
- `commit`: local history-তে snapshot রাখে। `push`: commit GitHub-এ পাঠায়।
- `pull --ff-only`: remote update নামায়; history আলাদা হলে থামে।

## 4. Branch ও Pull Request

```powershell
git switch main
git pull --ff-only
git switch -c feature/login-improvement
# কাজ শেষে
git add .
git commit -m "feat: improve login flow"
git push -u origin feature/login-improvement
```

GitHub → **Pull requests → New pull request**; base `main`, compare তোমার feature branch। পরিবর্তন review করে PR merge করো। পরে:

```powershell
git switch main
git pull --ff-only
git branch -d feature/login-improvement
```

`git branch` local branch দেখায়; `git branch -a` local ও remote-tracking branch দেখায়।

## 5. Commit message-এর প্রচলিত ধরন

| Prefix | বাংলা অর্থ/ব্যবহার | উদাহরণ |
|---|---|---|
| `feat:` | নতুন feature | `feat: add barcode scan` |
| `fix:` | bug ঠিক করা | `fix: prevent duplicate payment` |
| `docs:` | documentation | `docs: explain Android setup` |
| `test:` | test যোগ/পরিবর্তন | `test: cover mobile login` |
| `refactor:` | behavior না বদলে code গুছানো | `refactor: simplify URL check` |
| `style:` | formatting/UI style | `style: align login buttons` |
| `perf:` | performance উন্নতি | `perf: cache product lookup` |
| `build:` | dependency/build config | `build: update Gradle plugin` |
| `ci:` | CI workflow | `ci: run Flutter analysis` |
| `chore:` | maintenance | `chore: update lockfile` |
| `revert:` | আগের commit ফিরিয়ে দেওয়া | `revert: ...` |

এগুলো convention, বাধ্যতামূলক Git rule নয়। Message-এ secret/password/API key লিখবে না।

## 6. History, branch, remote দেখা

```powershell
git log --oneline --graph --decorate --all
git show --stat HEAD
git show COMMIT_ID
git blame path\to\file
git diff main..feature/login-improvement
git branch -vv
git remote -v
```

`HEAD` হলো বর্তমান checkout করা commit।

## 7. Merge conflict

Conflict হলে আগে থামো ও `git status` দেখো। Conflict file-এ marker এমন হতে পারে:

```text
<<<<<<< HEAD
বর্তমান branch-এর অংশ
=======
অন্য branch-এর অংশ
>>>>>>> branch-name
```

চূড়ান্ত সঠিক content হাতে edit করে marker মুছে save করো। সব conflict file ঠিক করে:

```powershell
git add path\to\resolved-file
git status
git commit
git push
```

Merge বাতিল করতে (merge চলমান থাকলে): `git merge --abort`। Rebase বাতিল করতে: `git rebase --abort`। Conflict বুঝতে না পারলে `push --force` বা `reset --hard` চালিও না।

## 8. Undo ও backup

| কাজ | Command | সাবধানতা |
|---|---|---|
| unstaged edit বাতিল | `git restore path\file` | ওই file-এর local edit মুছে যায় |
| stage থেকে নামানো, edit রাখা | `git restore --staged path\file` | ভুল staging-এর জন্য |
| shared commit undo | `git revert COMMIT_ID` | নতুন undo commit তৈরি করে |
| সাময়িক save | `git stash push -m "message"` | পরে `git stash list`, `git stash pop` |
| commit history সরানো | `git reset ...` | history বদলায়; আগে বুঝে নাও |

`git reset --hard` uncommitted data মুছতে পারে। `git push --force` remote history-ও বদলাতে পারে। নিশ্চিত না হলে ব্যবহার করো না।

## 9. `.gitignore`, secret ও generated files

উদাহরণ:

```gitignore
.env
.env.*
!.env.example
vendor/
node_modules/
.dart_tool/
build/
```

Framework অনুযায়ী ignore list ঠিক করো। `.env`-এ password/database credential/API key থাকতে পারে—push কোরো না। `.gitignore` আগে থেকেই tracked file-কে নিজে থেকে বাদ দেয় না। Tracked secret সরালে history-ও পরীক্ষা এবং secret rotate করতে হয়। Build output, dependency folder ও user data অকারণে commit কোরো না।

## 10. GitHub-এর অংশগুলো কী কাজে লাগে

- **Code**: files, commits, branches, tags।
- **Issues**: bug/feature task, steps, labels, assignee।
- **Pull requests**: code review, discussion, automated checks ও merge।
- **Actions**: `.github/workflows/*.yml` দিয়ে test/lint/build automation। Red হলে Actions → failed run → failed job → প্রথম failing step-এর log দেখো।
- **Settings**: access, branch rules, secrets, Actions permissions, visibility।
- **Tags/Releases**: version snapshot; যেমন `v1.0.0`। `git tag -a v1.0.0 -m "Release v1.0.0"`, তারপর `git push origin v1.0.0`।
- **Projects/Discussions/Wiki**: planning, Q&A, project notes।
- **Packages/Pages/Deployments**: artifact hosting/site deployment; credentials ও আলাদা setup লাগতে পারে।

Green check মানে workflow-এ থাকা checks pass করেছে; পুরো app bug-free তার নিশ্চয়তা নয়।

## 11. এই repository-র command map

- Remote: `origin` → `https://github.com/RaselIsalm/posapps.git`
- Branch: `main`
- Workflow: `.github/workflows/ci.yml`
- Laravel root: repository root; Flutter root: `mobile/`
- Current CI: Laravel tests, Flutter analyzer; `mobile/test` থাকলে Flutter tests। APK CI-তে build হয় না।

Local Android debug build:

```powershell
Set-Location C:\Users\USER\Desktop\posapps-main\mobile
flutter pub get
flutter analyze
flutter build apk --debug
```

## 12. চার সপ্তাহের study plan

1. **Week 1 — Basics**: `status`, `add`, `diff`, `commit`, `log`; practice repo-তে ছোট commit করো।
2. **Week 2 — Remote**: empty repo, `remote -v`, `push`, `clone`, `fetch`, `pull`; browser থেকে README edit করে update নামাও।
3. **Week 3 — Teamwork**: branch, PR, review, merge; test repo-তে conflict বানিয়ে resolve ও `merge --abort` অনুশীলন করো।
4. **Week 4 — Safety/automation**: `.gitignore`, stash, revert, tag/release, Actions YAML ও logs পড়ো।

## Quick reference

```powershell
git status
git add .
git diff
git diff --staged
git commit -m "fix: describe change"
git log --oneline --graph
git branch
git switch -c feature/name
git remote -v
git pull --ff-only
git push -u origin feature/name
git stash list
```

**Golden rule:** push/merge/undo-র আগে `git status` ও `git diff` দেখে নাও। Secret stage/push কোরো না; destructive history command না বুঝে চালিও না।
