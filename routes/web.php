<?php
/**
 * Web Routes
 * Define all web routes for the application
 */

// Authentication routes
Router::any('/login', 'AuthController', 'login');
Router::post('/logout', 'AuthController', 'logout');
Router::any('/register', 'AuthController', 'register');
Router::post('/change-password', 'AuthController', 'changePassword');
Router::post('/check-username', 'AuthController', 'checkUsername');

// User management routes
Router::get('/users', 'UserController', 'index');
Router::get('/users/{id}', 'UserController', 'show');
Router::any('/users/create', 'UserController', 'create');
Router::any('/users/{id}/edit', 'UserController', 'edit');
Router::post('/users/{id}/delete', 'UserController', 'delete');
Router::post('/users/{id}/toggle-status', 'UserController', 'toggleStatus');
Router::get('/users/search', 'UserController', 'search');
Router::get('/users/statistics', 'UserController', 'statistics');
Router::get('/users/export', 'UserController', 'export');

// Berita Acara routes
Router::get('/berita-acara', 'BeritaAcaraController', 'index');
Router::get('/berita-acara/{id}', 'BeritaAcaraController', 'show');
Router::any('/berita-acara/create', 'BeritaAcaraController', 'create');
Router::any('/berita-acara/{id}/edit', 'BeritaAcaraController', 'edit');
Router::post('/berita-acara/{id}/delete', 'BeritaAcaraController', 'delete');
Router::get('/berita-acara/search', 'BeritaAcaraController', 'search');
Router::get('/berita-acara/statistics', 'BeritaAcaraController', 'statistics');
Router::get('/berita-acara/{id}/pdf', 'BeritaAcaraController', 'generatePDF');
Router::get('/berita-acara/export', 'BeritaAcaraController', 'export');

// Kendaraan routes
Router::get('/kendaraan', 'KendaraanController', 'index');
Router::get('/kendaraan/{id}', 'KendaraanController', 'show');
Router::any('/kendaraan/create', 'KendaraanController', 'create');
Router::any('/kendaraan/{id}/edit', 'KendaraanController', 'edit');
Router::post('/kendaraan/{id}/delete', 'KendaraanController', 'delete');
Router::post('/kendaraan/{id}/update-status', 'KendaraanController', 'updateStatus');
Router::get('/kendaraan/search', 'KendaraanController', 'search');
Router::get('/kendaraan/statistics', 'KendaraanController', 'statistics');
Router::get('/kendaraan/export', 'KendaraanController', 'export');

// Dashboard routes
Router::get('/dashboard', 'DashboardController', 'index');
Router::get('/dashboard/statistics', 'DashboardController', 'statistics');

// Settings routes
Router::any('/settings', 'SettingsController', 'index');
Router::post('/settings/update', 'SettingsController', 'update');

// API routes (for AJAX calls)
Router::get('/api/users/search', 'UserController', 'search');
Router::get('/api/berita-acara/search', 'BeritaAcaraController', 'search');
Router::get('/api/kendaraan/search', 'KendaraanController', 'search');
Router::get('/api/dashboard/stats', 'DashboardController', 'getStats');

// File upload routes
Router::post('/upload/logo', 'FileController', 'uploadLogo');
Router::post('/upload/document', 'FileController', 'uploadDocument');

// Report routes
Router::get('/reports/berita-acara', 'ReportController', 'beritaAcara');
Router::get('/reports/kendaraan', 'ReportController', 'kendaraan');
Router::get('/reports/users', 'ReportController', 'users');
Router::post('/reports/generate', 'ReportController', 'generate');
?>
