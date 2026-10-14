<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('pa02:prepare', function () {
    if (config('database.default') !== 'mysql' || app()->environment('testing')) {
        $this->error('Solo se permite la conexión mysql local de la aplicación.');

        return 1;
    }
    $identity = DB::selectOne('SELECT DATABASE() AS db, @@port AS port, VERSION() AS version');
    if ($identity->db !== 'pa02_blog' || (int) $identity->port !== 3306) {
        $this->error('Base o puerto inesperados. No se ejecutó ninguna migración.');

        return 1;
    }
    $tables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'pa02_blog'");
    $path = base_path('../evidencias');
    file_put_contents($path.'/database-verification.txt', json_encode($identity, JSON_PRETTY_PRINT)."\n".date(DATE_ATOM)."\n");
    if (count($tables) > 0) {
        $this->error('La base no está vacía. No se permite migrate:fresh. Revisa los datos existentes.');

        return 1;
    }
    $this->info('Base nueva y vacía verificada: pa02_blog, puerto 3306.');
    $code = $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
    if ($code !== 0) {
        return $code;
    }
    file_put_contents($path.'/migrations.txt', Artisan::call('migrate:status') === 0 ? Artisan::output() : 'Error');
    file_put_contents($path.'/seed.txt', 'Usuarios: '.User::count().' Categorías: '.Category::count().' Noticias: '.Post::count().' Publicadas: '.Post::published()->count().' Borradores: '.Post::where('status', 'draft')->count()."\n");
    $this->info('Migraciones y seed completados. Evidencias guardadas.');

    return 0;
})->purpose('Verifica base exclusiva antes de migrar y sembrar el PA02');
