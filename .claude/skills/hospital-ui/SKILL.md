---
name: hospital-ui
description: Guía de UI/UX para el frontend de sistema-hospitalario (Blade + Bootstrap 5 + Bootstrap Icons + Yajra DataTables, con Vue 3 solo en widgets puntuales). Úsala SIEMPRE que crees o modifiques algo visible en resources/views, resources/css o resources/js — páginas, formularios, tablas, columnas de DataTable, mensajes, estados vacíos, errores, iconos, espaciado, accesibilidad o textos de interfaz.
---

# UI/UX en sistema-hospitalario

Frontend: Blade + **Bootstrap 5.3** + **Bootstrap Icons** (`bi bi-*`), listados con **Yajra DataTables** (`laravel-datatables-vite`), build con Vite. **Vue 3** se usa solo para widgets interactivos (`DualListbox`, `OptionsTree`, `PasswordStrength`), montados vía `data-vue-component` y registrados en `resources/js/app.js`. No hay Vuetify, Pinia, vue-i18n ni Tailwind: no los introduzcas.

## Regla nº1: copia el patrón existente

Toma como referencia `resources/views/admin/users/` (`index`, `form`, `columns/*`).

| Necesidad | Usa | Dónde |
|---|---|---|
| Layout autenticado | `@extends('layouts.app')` con `@section('title')`, `page-title`, `page-description`, `page-action`, `content` | `layouts/app.blade.php` |
| Layout login/errores | `layouts.guest` | `auth/*`, `errors/403.blade.php` |
| Título de página | Se declara en las secciones, **no** pongas otro `<h1>` en el contenido | `layouts/app.blade.php` |
| Flash y errores de validación | Ya los pinta `partials/messages.blade.php`; solo envía `->with('success', ...)` | controladores |
| Estado vacío | `<x-empty-state message="..."/>` | `components/empty-state.blade.php` |
| Listado | `$dataTable->table(['class' => 'table table-hover w-100'])` dentro de `.card.border-0.shadow-sm > .card-body > .table-responsive`, y `{{ $dataTable->scripts() }}` en `@push('scripts')` | `admin/users/index.blade.php` |
| Celdas/acciones de tabla | Parcial Blade en `admin/<recurso>/columns/*.blade.php` renderizado desde la clase DataTable | `columns/action`, `status`, `roles` |
| Selección múltiple / árbol / contraseña | Componentes Vue existentes | `resources/js/components` |

## Páginas

- Botón principal en la sección `page-action`: `btn btn-primary` con icono (`bi-plus-lg`); en formularios, `btn btn-outline-secondary` con `bi-arrow-left` "Regresar".
- Contenido dentro de `card border-0 shadow-sm`. Formularios: `card-body p-4`, secciones con `<h2 class="h5 mb-4">`, rejilla `row g-3` + `col-md-6` / `col-12`.
- Una pantalla nueva implica: ruta + permiso (ver skill `laravel-expert`) + `Option` de menú con icono `bi-*`.

## Formularios

- `<label class="form-label" for>` + `form-control` / `form-select` / `form-check`, siempre con `id` enlazado y `required` cuando aplique.
- Repoblar con `old('campo', $modelo->campo ?? '')`; `@csrf` y `@method('PUT'|'DELETE')`; `enctype="multipart/form-data"` si hay archivos.
- Errores de campo: `is-invalid` + `<div class="invalid-feedback">{{ $message }}</div>` con `@error`. El resumen global ya lo muestra `messages`.
- Un solo Blade `form.blade.php` para crear y editar (`$editing = isset($modelo)`), como en usuarios.
- Botones de envío con texto de acción claro ("Guardar", "Crear usuario"); evita doble envío en acciones lentas.

## Tablas y acciones

- Columnas de acción: `d-flex gap-2`, botones `btn btn-sm btn-outline-primary|danger` con icono y texto `<span class="d-none d-sm-inline">` (el texto se oculta en móvil).
- Estado como `badge text-bg-success|warning|danger|secondary` mediante `match` en el parcial.
- Acciones destructivas: formulario `DELETE` con confirmación (hoy `confirm()` nativo con mensaje "¿Eliminar …? Esta acción no se puede deshacer."). Mantén ese patrón; si se pide modal, usar el modal de Bootstrap, no una librería nueva.
- Si usas HTML en columnas, decláralas en `rawColumns` y escapa todo dato del usuario con `{{ }}`.

## Sistema visual

- Colores solo con las clases semánticas de Bootstrap (`primary`, `success`, `danger`, `warning`, `secondary`, `text-muted`). Estilos propios en `resources/css/app.css`; no pongas `style=""` ni colores hex sueltos en vistas.
- Iconos únicamente de Bootstrap Icons (`bi bi-…`), con `aria-label` o texto visible cuando el icono es el único contenido.
- Fuente base 16px (`--accessible-font-size`); respeta el tamaño accesible, no lo reduzcas.
- Layout con sidebar (`.app-sidebar`, colapsable en `<lg` con `data-sidebar-toggle`) y cabecera con avatar; no recrees navegación propia, el menú sale de `MenuService`.

## Accesibilidad y responsive

- Interfaz clínica: usuarios con prisa y pocos conocimientos técnicos. Etiquetas claras, mensajes concretos ("Usuario creado. La invitación fue enviada por correo."), sin jerga.
- `role="alert"`/`role="status"` en mensajes, `aria-label` en botones solo-icono y `<nav aria-label>`, `alt` en imágenes, contraste suficiente y foco visible (no quites `outline`).
- Mobile-first con rejilla Bootstrap (`col-12 col-md-*`), tablas en `.table-responsive`, texto de botones oculto en `<sm`.
- Datos de pacientes: no los pongas en `title=""`, URLs ni atributos `data-*` visibles al inspeccionar si no hace falta.

## Textos

Interfaz en **español** (`<html lang="es">`), tuteo evitado: se trata de usted ("Complete", "Revise la información"). Hoy los textos van directamente en las vistas/controladores; no introduzcas un sistema de i18n a menos que se pida. Capitaliza solo la primera palabra de títulos y botones.

## Vue (solo si hace falta)

`<script setup>` con Vue 3 en `resources/js/components/`, registrado en el mapa `components` de `app.js` y montado con `<div data-vue-component="Nombre" data-props='@json([...])'></div>`. El componente recibe props JSON y se sincroniza con el formulario mediante inputs ocultos, para que el envío siga siendo un POST normal de Laravel.

## Checklist

- [ ] ¿Extiende `layouts.app` con title/page-title/description y sin `<h1>` duplicado?
- [ ] ¿Reutiliza `messages`, `empty-state`, columnas parciales y componentes Vue existentes?
- [ ] ¿Campos con label+id, `old()`, `@error` y `@csrf`?
- [ ] ¿Solo clases Bootstrap/`bi-*`, sin estilos inline ni librerías nuevas?
- [ ] ¿Se ve bien en móvil y es operable por teclado?
- [ ] ¿Textos en español, claros y de usted?
- [ ] ¿Compilado con `npm run build` (o `dev`) sin errores cuando se tocó JS/CSS?
