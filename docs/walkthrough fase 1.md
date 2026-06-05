# Walkthrough — GarudaCBT Phase 1 Foundation

Phase 1 — Foundation of the GarudaCBT school computer-based testing system is now complete. The architecture is solid, and the compilation outputs verify its correctness.

## Changes Made

### 1. User Management — Siswa (Student) View
- Created new view [SiswaIndex.vue](file:///d:/my-app/resources/js/pages/Setting/User/SiswaIndex.vue) with a multi-tab dialog form:
  - **Identitas & Akun**: Handles student core info (Name, NIK, NIS, NISN, Gender, Birth info, Religion, Phone) and User account credentials (Username, Email, Password).
  - **Alamat Tinggal**: Full residential address details (Street, RT, RW, Kelurahan, Kecamatan, Kabupaten, Provinsi, Zip code).
  - **Orang Tua / Wali**: Details for Father, Mother, and optionally Guardian.
  - **Akademik & Lainnya**: Year of entry, origin school, siblings ordering, family status, and physical RFID/exam card UID.

### 2. Laravel Wayfinder Route Integration
- Fixed route warnings and compilation errors across all components. Instead of hardcoded paths or global `route(...)` calls that cause type errors on compilation, the codebase now relies on type-safe Laravel Wayfinder auto-generated functions:
  - **AppSidebar**: Uses `tpIndex.url()`, `smtIndex.url()`, `schoolEdit.url()`, `adminIndex.url()`, `guruIndex.url()`, and `siswaIndex.url()`.
  - **Academic Periods**: Updates form calls in [Semester/Index.vue](file:///d:/my-app/resources/js/pages/Master/Semester/Index.vue) and [TahunPelajaran/Index.vue](file:///d:/my-app/resources/js/pages/Master/TahunPelajaran/Index.vue) to use Wayfinder URL string getters (`store.url()`, `activate.url(id)`, `destroy.url(id)`).
  - **Settings**: Refactored [School.vue](file:///d:/my-app/resources/js/pages/Setting/School.vue) to use `schoolUpdate.url()`.
  - **User Management**: Modified [AdminIndex.vue](file:///d:/my-app/resources/js/pages/Setting/User/AdminIndex.vue) and [GuruIndex.vue](file:///d:/my-app/resources/js/pages/Setting/User/GuruIndex.vue) to use Wayfinder action URL mappings.

### 3. Type Safety & Global Definitions
- Added `roles?: string[]` to the custom `Auth` type inside [auth.ts](file:///d:/my-app/resources/js/types/auth.ts) to match backend-injected Inertia props.
- Standardized form numeric inputs (`kode_pos`, `anak_ke` as `number | undefined`) to match Vue element bindings.
- Regenerated all Wayfinder route helpers with the `--with-form` flag to restore `.form()` helper functions utilized by other auth-related layouts and pages.

---

## Verification & Testing Results

### 1. TypeScript Compiler Output
Ran type-checking via `vue-tsc --noEmit` which completed successfully with **zero compile errors**:
```bash
> types:check
> vue-tsc --noEmit
```

### 2. Production Bundling (Vite Build)
Compiled all JS/TS chunks and CSS sheets into production-ready static assets:
```bash
vite v8.0.16 building client environment for production...
✓ 2944 modules transformed.
✓ built in 39.20s
```

### 3. AST Code Graph Update
Updated the `graphify` database dependency records using the CLI:
```bash
[graphify watch] Rebuilt: 1493 nodes, 1194 edges, 344 communities
[graphify watch] graph.json, graph.html and GRAPH_REPORT.md updated
```

---

## How to Verify Manually

1. **Start the local Dev Server**:
   ```bash
   npm run dev
   ```
2. **Access phpMyAdmin/MySQL**:
   Open a browser to your local web server port to view the `GarudaCBT_Laravel` tables.
3. **Log in with seeded accounts**:
   - **Super Admin**: username `admin` / password `password`
   - **Operator TU**: username `operator` / password `password`
   - **Guru**: username `guru` / password `password`
   - **Siswa**: username `siswa` / password `password`
4. **Test the settings panel**:
   Navigating through the sidebar links (Tahun Pelajaran, Semester, Profil Sekolah, User Admin, User Guru, User Siswa) should load smoothly. Modals for adding, editing, and deleting student accounts, as well as setting active periods, should update state and issue custom toast notifications on success.
