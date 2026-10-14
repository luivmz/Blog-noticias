<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->category = Category::create(['name' => 'Tecnología', 'slug' => 'tecnologia']);
        $this->admin = User::create(['name' => 'Administración', 'email' => 'admin@example.test', 'password' => 'Admin123!']);
    }

    private function data(array $overrides = []): array
    {
        return array_merge(['category_id' => $this->category->id, 'title' => 'Una noticia del campus', 'summary' => 'Un resumen suficientemente claro para la noticia.', 'content' => 'Primer párrafo original con contenido para realizar la prueba.', 'status' => 'published'], $overrides);
    }

    private function makePost(array $overrides = []): Post
    {
        return Post::create($this->data($overrides));
    }

    public function test_accessibility_panel_has_named_controls_and_preferences(): void
    {
        $response = $this->get('/');
        $response->assertSee('id="accessibility-toggle"', false)
            ->assertSee('aria-controls="accessibility-panel"', false)
            ->assertSee('id="accessibility-panel"', false)
            ->assertSee('Disminuir texto')->assertSee('Aumentar texto')
            ->assertSee('Tamaño del texto')->assertSee('Alto contraste')
            ->assertSee('Oscuro')->assertSee('Lectura cómoda')
            ->assertSee('Activar ayuda por voz')->assertSee('Restablecer preferencias')
            ->assertSee('aria-pressed="false"', false)->assertSee('js/accessibility.js');
    }

    public function test_news_detail_has_optional_reader_and_separate_readable_content(): void
    {
        $post = $this->makePost();
        $this->get('/noticias/'.$post->slug)->assertOk()
            ->assertSee('Escuchar esta noticia')->assertSee('>Escuchar</button>', false)
            ->assertSee('>Pausar</button>', false)->assertSee('>Continuar</button>', false)
            ->assertSee('>Detener</button>', false)->assertSee('data-news-title', false)
            ->assertSee('data-news-summary', false)->assertSee('data-news-content', false)
            ->assertSee('id="reader-status" role="status"', false);
        $this->get('/')->assertDontSee('id="read-start"', false);
    }

    public function test_invalid_login_has_focusable_summary_and_linked_field_error(): void
    {
        $this->from('/login')->post('/login', ['email' => 'invalid', 'password' => ''])
            ->assertRedirect('/login');
        $this->get('/login')->assertSee('id="error-summary"', false)
            ->assertSee('tabindex="-1"', false)->assertSee('Hay 2 campos que necesitan revisión.')
            ->assertSee('href="#email"', false)->assertSee('aria-invalid="true"', false)
            ->assertSee('aria-describedby="email-error"', false)->assertSee('id="email-error"', false);
    }

    public function test_invalid_create_and_edit_preserve_accessible_error_summary(): void
    {
        $this->actingAs($this->admin);
        $post = $this->makePost();
        foreach ([['post', '/admin/noticias', '/admin/noticias/create'], ['put', '/admin/noticias/'.$post->id, '/admin/noticias/'.$post->id.'/edit']] as [$method, $action, $form]) {
            $this->from($form)->{$method}($action, $this->data(['title' => 'Mal']))->assertRedirect($form);
            $this->get($form)->assertSee('id="error-summary"', false)
                ->assertSee('Hay 1 campo que necesita revisión.')
                ->assertSee('href="#title"', false)->assertSee('aria-invalid="true"', false)
                ->assertSee('aria-describedby="title-help title-error"', false)
                ->assertSee('id="title-error"', false)->assertSee('value="Mal"', false);
        }
    }

    public function test_home_is_available_and_has_editorial_identity(): void
    {
        $this->get('/')->assertOk()->assertSee('Entrelineas')->assertSee('Historias que merecen una pausa.');
    }

    public function test_home_shows_published_and_hides_drafts(): void
    {
        $public = $this->makePost();
        $draft = $this->makePost(['title' => 'Una noticia reservada', 'status' => 'draft']);
        $this->get('/')->assertSee($public->title)->assertDontSee($draft->title);
    }

    public function test_news_list_only_contains_published(): void
    {
        $this->makePost();
        $draft = $this->makePost(['title' => 'Contenido de borrador', 'status' => 'draft']);
        $this->get('/noticias')->assertOk()->assertSee('1 noticias encontradas')->assertDontSee($draft->title);
    }

    public function test_pagination_limits_results_to_six(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->makePost(['title' => 'Noticia número '.$i]);
        }$this->get('/noticias')->assertViewHas('posts', fn ($p) => $p->count() === 6 && $p->total() === 7)->assertSee('Siguiente');
        $this->get('/noticias?page=2')->assertViewHas('posts', fn ($p) => $p->count() === 1);
    }

    public function test_search_finds_title(): void
    {
        $this->makePost(['title' => 'Robótica en el campus']);
        $this->makePost(['title' => 'Lectura compartida']);
        $this->get('/noticias?q=Robótica')->assertSee('Robótica en el campus')->assertDontSee('Lectura compartida');
    }

    public function test_search_finds_summary(): void
    {
        $this->makePost(['summary' => 'Experimentos sobre telescopios y observación.']);
        $this->get('/noticias?q=telescopios')->assertViewHas('posts', fn ($p) => $p->total() === 1);
    }

    public function test_category_filter(): void
    {
        $other = Category::create(['name' => 'Ciencia', 'slug' => 'ciencia']);
        $this->makePost();
        $this->makePost(['category_id' => $other->id, 'title' => 'Un artículo científico']);
        $this->get('/noticias?category='.$other->id)->assertSee('Un artículo científico')->assertDontSee('Una noticia del campus');
    }

    public function test_search_and_category_are_combined_without_leaking_drafts(): void
    {
        $other = Category::create(['name' => 'Ciencia', 'slug' => 'ciencia']);
        $this->makePost(['title' => 'Proyecto robot']);
        $this->makePost(['title' => 'Proyecto telescopio', 'category_id' => $other->id]);
        $this->makePost(['title' => 'Proyecto secreto', 'status' => 'draft']);
        $this->get('/noticias?q=Proyecto&category='.$this->category->id)->assertSee('Proyecto robot')->assertDontSee('Proyecto telescopio')->assertDontSee('Proyecto secreto');
    }

    public function test_pagination_retains_query_string(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->makePost(['title' => 'Proyecto '.$i]);
        }$this->get('/noticias?q=Proyecto&category='.$this->category->id)->assertSee('q=Proyecto&amp;category='.$this->category->id.'&amp;page=2', false);
    }

    public function test_empty_search_has_clear_message(): void
    {
        $this->get('/noticias?q=ausente')->assertSee('No se encontraron noticias con los criterios seleccionados.');
    }

    public function test_published_detail_uses_slug_and_escapes_content(): void
    {
        $post = $this->makePost(['content' => 'Texto seguro de noticia con <script>alert(1)</script> al final.']);
        $this->get('/noticias/'.$post->slug)->assertOk()->assertSee($post->title)->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_draft_and_missing_details_return_404(): void
    {
        $post = $this->makePost(['status' => 'draft']);
        $this->get('/noticias/'.$post->slug)->assertNotFound();
        $this->get('/noticias/no-existe')->assertNotFound();
    }

    public function test_categories_count_only_public_news(): void
    {
        $this->makePost();
        $this->makePost(['status' => 'draft']);
        $this->get('/categorias')->assertOk()->assertSee('1 noticias publicadas');
    }

    public function test_login_form_is_available(): void
    {
        $this->get('/login')->assertOk()->assertSee('Correo electrónico')->assertSee('Contraseña');
    }

    public function test_valid_login_authenticates_and_redirects(): void
    {
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'Admin123!'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_invalid_login_is_rejected_without_password_flash(): void
    {
        $this->from('/login')->post('/login', ['email' => $this->admin->email, 'password' => 'incorrecta'])->assertRedirect('/login')->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_login_requires_valid_email_and_password(): void
    {
        $this->post('/login', ['email' => 'invalido'])->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_guest_cannot_access_any_admin_action(): void
    {
        $p = $this->makePost();
        foreach (['/admin', '/admin/noticias', '/admin/noticias/create', '/admin/noticias/'.$p->id.'/edit'] as $url) {
            $this->get($url)->assertRedirect('/login');
        }$this->post('/admin/noticias', $this->data())->assertRedirect('/login');
        $this->put('/admin/noticias/'.$p->id, $this->data())->assertRedirect('/login');
        $this->delete('/admin/noticias/'.$p->id)->assertRedirect('/login');
        $this->assertDatabaseCount('posts', 1);
    }

    public function test_admin_dashboard_has_accurate_summary(): void
    {
        $this->makePost();
        $this->makePost(['status' => 'draft']);
        $this->actingAs($this->admin)->get('/admin')->assertOk()->assertViewHas('total', 2)->assertViewHas('published', 1)->assertViewHas('drafts', 1);
    }

    public function test_logout_closes_session(): void
    {
        $this->actingAs($this->admin)->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_admin_index_includes_both_states_and_table_headers(): void
    {
        $this->makePost();
        $this->makePost(['status' => 'draft']);
        $this->actingAs($this->admin)->get('/admin/noticias')->assertOk()->assertSee('Publicado')->assertSee('Borrador')->assertSee('scope="col"', false)->assertSee('<caption>', false);
    }

    public function test_create_form_has_labels_and_help(): void
    {
        $r = $this->actingAs($this->admin)->get('/admin/noticias/create')->assertOk();
        foreach (['title', 'summary', 'content', 'category_id', 'status'] as $id) {
            $r->assertSee('for="'.$id.'"', false)->assertSee('id="'.$id.'"', false);
        }$r->assertSee('Entre 10 y 300 caracteres.')->assertSee('Cancelar');
    }

    public function test_valid_store_creates_news_and_flash(): void
    {
        $this->actingAs($this->admin)->post('/admin/noticias', $this->data())->assertRedirect('/admin/noticias')->assertSessionHas('status', 'Noticia creada correctamente.');
        $this->assertDatabaseHas('posts', ['title' => 'Una noticia del campus', 'status' => 'published']);
        $this->assertNotNull(Post::first()->published_at);
    }

    #[DataProvider('invalidData')]
    public function test_invalid_store_does_not_persist(string $field, mixed $value): void
    {
        $this->actingAs($this->admin)->from('/admin/noticias/create')->post('/admin/noticias', $this->data([$field => $value]))->assertRedirect('/admin/noticias/create')->assertSessionHasErrors($field);
        $this->assertDatabaseCount('posts', 0);
    }

    public static function invalidData(): array
    {
        return [['title', '1234'], ['title', str_repeat('a', 151)], ['summary', '123456789'], ['summary', str_repeat('a', 301)], ['content', str_repeat('a', 29)], ['category_id', 999], ['status', 'otro']];
    }

    public function test_validation_accepts_exact_boundaries(): void
    {
        $this->actingAs($this->admin);
        foreach ([[5, 10, 30], [150, 300, 30]] as [$t,$s,$c]) {
            $this->post('/admin/noticias', $this->data(['title' => str_repeat('a', $t), 'summary' => str_repeat('a', $s), 'content' => str_repeat('a', $c)]))->assertSessionHasNoErrors();
        }$this->assertDatabaseCount('posts', 2);
    }

    public function test_required_fields_are_rejected_and_errors_are_associated(): void
    {
        $this->actingAs($this->admin)->from('/admin/noticias/create')->post('/admin/noticias', [])->assertSessionHasErrors(['title', 'summary', 'content', 'category_id', 'status']);
        $this->get('/admin/noticias/create')->assertSee('aria-invalid="true"', false)->assertSee('title-help title-error', false)->assertSee('id="title-error"', false)->assertSee('El campo título es obligatorio.');
        $html = $this->get('/admin/noticias/create')->getContent();
        preg_match('/<h([1-6])(?:\s|>)/', $html, $heading);
        $this->assertSame('1', $heading[1]);
    }

    public function test_invalid_store_preserves_written_values(): void
    {
        $this->actingAs($this->admin)->from('/admin/noticias/create')->post('/admin/noticias', $this->data(['title' => 'abc']))->assertSessionHasInput('summary', $this->data()['summary']);
        $this->get('/admin/noticias/create')->assertSee('value="abc"', false)->assertSee($this->data()['content']);
    }

    public function test_edit_displays_current_and_old_values(): void
    {
        $p = $this->makePost();
        $this->actingAs($this->admin)->get('/admin/noticias/'.$p->id.'/edit')->assertOk()->assertSee($p->title);
        $this->from('/admin/noticias/'.$p->id.'/edit')->put('/admin/noticias/'.$p->id, $this->data(['title' => 'Nueva versión válida', 'summary' => 'corta']))->assertSessionHasErrors('summary');
        $this->get('/admin/noticias/'.$p->id.'/edit')->assertSee('Nueva versión válida')->assertSee('corta');
    }

    public function test_update_changes_fields_and_publication_state(): void
    {
        $p = $this->makePost(['status' => 'draft']);
        $this->actingAs($this->admin)->put('/admin/noticias/'.$p->id, $this->data(['title' => 'Una edición revisada']))->assertRedirect('/admin/noticias')->assertSessionHas('status', 'Noticia actualizada correctamente.');
        $p->refresh();
        $this->assertSame('una-edicion-revisada', $p->slug);
        $this->assertNotNull($p->published_at);
        $this->get('/noticias/'.$p->slug)->assertOk();
    }

    public function test_invalid_update_does_not_change_database(): void
    {
        $p = $this->makePost();
        $this->actingAs($this->admin)->put('/admin/noticias/'.$p->id, $this->data(['title' => 'a']))->assertSessionHasErrors('title');
        $this->assertSame('Una noticia del campus', $p->fresh()->title);
    }

    public function test_delete_removes_news_and_has_confirmation_markup(): void
    {
        $p = $this->makePost();
        $this->actingAs($this->admin)->get('/admin/noticias')->assertSee('¿Seguro que deseas eliminar esta noticia?')->assertSee('value="DELETE"', false);
        $this->delete('/admin/noticias/'.$p->id)->assertRedirect('/admin/noticias')->assertSessionHas('status', 'Noticia eliminada correctamente.');
        $this->assertDatabaseMissing('posts', ['id' => $p->id]);
    }

    public function test_duplicate_titles_have_unique_slugs_and_unchanged_edit_keeps_slug(): void
    {
        $a = $this->makePost();
        $b = $this->makePost();
        $this->assertSame('una-noticia-del-campus', $a->slug);
        $this->assertSame('una-noticia-del-campus-2', $b->slug);
        $a->update(['summary' => 'Un resumen revisado sin cambiar título.']);
        $this->assertSame('una-noticia-del-campus', $a->fresh()->slug);
    }

    public function test_publication_date_is_set_once_and_draft_is_hidden_again(): void
    {
        $p = $this->makePost(['status' => 'draft']);
        $this->assertNull($p->published_at);
        $p->update(['status' => 'published']);
        $date = $p->published_at->toISOString();
        $this->travel(1)->days();
        $p->update(['summary' => 'Cambio posterior sin alterar la publicación.']);
        $this->assertSame($date, $p->fresh()->published_at->toISOString());
        $p->update(['status' => 'draft']);
        $this->get('/noticias/'.$p->slug)->assertNotFound();
    }

    public function test_public_pages_have_accessible_structure_and_active_navigation(): void
    {
        $p = $this->makePost();
        foreach (['/', '/noticias', '/categorias', '/login', '/noticias/'.$p->slug] as $url) {
            $r = $this->get($url)->assertOk()->assertSee('lang="es"', false)->assertSee('href="#contenido-principal"', false)->assertSee('<main id="contenido-principal"', false)->assertSee('aria-label="Navegación principal"', false)->assertSee('aria-current="page"', false);
            $this->assertSame(1, preg_match_all('/<h1(?:\s|>)/', $r->getContent()));
        }$this->get('/noticias')->assertSee('for="q"', false)->assertSee('for="category"', false)->assertSee('>Buscar</button>', false);
    }

    public function test_news_query_count_is_bounded_with_eager_loading(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->makePost(['title' => 'Noticia '.$i]);
        }DB::enableQueryLog();
        $this->get('/noticias')->assertOk();
        $queries = DB::getQueryLog();
        DB::disableQueryLog();
        $this->assertLessThanOrEqual(5, count($queries));
    }

    public function test_seed_contains_original_categories_news_and_local_admin(): void
    {
        $this->seed();
        $this->assertDatabaseCount('categories', 4);
        $this->assertDatabaseCount('posts', 18);
        $this->assertSame(16, Post::published()->count());
        $this->assertSame(2, Post::where('status', 'draft')->count());
        $this->assertDatabaseHas('users', ['email' => 'admin@example.test']);
    }

    public function test_csrf_rejects_post_without_token_in_non_testing_environment(): void
    {
        $this->app['env'] = 'local';
        $this->post('/login', ['email' => $this->admin->email, 'password' => 'Admin123!'])->assertStatus(419);
        $this->assertGuest();
    }
}
