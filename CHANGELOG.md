# Changelog

All notable changes to `modular-monolith-laravel-env` will be documented in this file.

## 0.2.1 - 2022-10-10
- PHPUnit-test via gitHub actions
- added .gitkeep to /tests/Feature
- the dotenv StoreBuilder so no longer created and updated parallel
- fixed a critical unix <-> windows bug

## 0.2.0 - 2022-10-10 - reading the .env (WIP / basically functional) 
- logic for reading the different files
- prefixing of the variables names by their SNAKE_CASE module name

## 0.1.0 - 2022-10-09 - hooking the Kernel (WIP / PreRelease-Version)
- basic implementation and project-structure
- implementing logic to hook into the Kernels
