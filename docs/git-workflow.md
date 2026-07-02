# Git Workflow

## Branches

This project uses the following branch flow:

~~~text
feature/* -> dev -> stage -> master
~~~

## Branch purpose

- `dev`: active development and local/DDEV-tested changes
- `stage`: staging/QA-ready changes
- `master`: production-ready changes only
- `feature/*`: isolated feature or task work

## Normal development flow

Create a feature branch from `dev`:

~~~bash
git checkout dev
git pull origin dev
git checkout -b feature/task-name
~~~

Commit work:

~~~bash
git add -A
git commit -m "Short clear commit message"
git push origin feature/task-name
~~~

Merge into `dev` after review/testing.

## Promote dev to stage

~~~bash
git checkout stage
git pull origin stage
git merge origin/dev
git push origin stage
~~~

Deploy stage with:

~~~bash
export DRUPAL_ENV=stage
./scripts/deploy-drupal.sh
~~~

## Promote stage to production

~~~bash
git checkout master
git pull origin master
git merge origin/stage
git push origin master
~~~

Deploy production with:

~~~bash
export DRUPAL_ENV=prod
./scripts/deploy-drupal.sh
~~~

## Before pushing Drupal changes

Run:

~~~bash
./scripts/check-drupal.sh
~~~

Then confirm:

~~~bash
git status
~~~
