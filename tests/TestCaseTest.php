<?php

namespace Panoscape\History\Tests;

use Orchestra\Testbench\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Panoscape\History\History;
use Panoscape\History\HistoryServiceProvider;
use Panoscape\History\Events\ModelChanged;

class TestCaseTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            HistoryServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'App\History' => History::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('history.console_enabled', true);
        $app['config']->set('history.test_enabled', true);
        $app['config']->set('history.attributes_blacklist', [
            User::class => [
                'password'
            ]
        ]);
        $app['config']->set('history.auth_guards', ['web','admin']);
        // custom auth guard mock
        $app['config']->set('auth.guards.admin.driver', 'admin-login');
        Auth::viaRequest('admin-login', function(Request $request) {
            return null;
        });

        $app['router']->post('articles', function(Request $request) {
            return Article::create(['title' => $request->title]);
        });
        $app['router']->put('articles/{id}', function(Request $request, $id) {
            $model = Article::find($id);
            $model->title = $request->title;
            $model->save();
            return $model;
        });
        $app['router']->delete('articles/{id}', function($id) {
            Article::destroy($id);
        });        
        $app['router']->post('articles/{id}/restore', function($id) {
            Article::withTrashed()->find($id)->restore();
        });        
        $app['router']->get('articles/{id}', function($id) {
            $model = Article::find($id);
            if(!is_null($model)) {
                event(new ModelChanged($model, 'Query Article ' . $model->title, $model->pluck('id')->toArray()));
            }
            return $model;
        });  
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {   
        $builder = $this->app['db']->connection()->getSchemaBuilder();

        $builder->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('password');
        });

        $builder->create('articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->softDeletes();
        });

        User::create(['name' => 'Esther', 'password' => '6ecd6a17b723']);

        $this->loadMigrationsFrom(realpath(__DIR__.'/../src/migrations'));
    }

    public function testHistory()
    {
        $content = ['title' => 'enim officiis omnis'];
        $this->json('POST', '/articles', $content)->assertJson($content);
        $history = History::first();
        $article = Article::first();
        $this->assertNotNull($history);
        $this->assertEquals(Article::class, $history->model_type);
        $this->assertEquals($article->id, $history->model_id);
        $this->assertEquals('Created Article ' . $content['title'], $history->message);
        $this->assertTrue($history->performed_at instanceof \Illuminate\Support\Carbon);
        $history->delete();

        $data = ['title' => 'eligendi fugiat culpa'];
        $this->json('PUT', '/articles/' . $article->id, $data)->assertJson($data);
        $history = History::first();
        $this->assertNotNull($history);
        $this->assertEquals($article->id, $history->model_id);
        $this->assertEquals('Updating Article ' . $content['title'], $history->message);
        $this->assertEquals([['key' => 'title', 'old' => 'enim officiis omnis', 'new' => 'eligendi fugiat culpa']], $history->meta);
        $history->delete();
        $article->refresh();

        $this->json('DELETE', '/articles/' . $article->id);
        $history = History::first();
        $this->assertNotNull($history);
        $this->assertEquals($article->id, $history->model_id);
        $this->assertEquals('Deleting Article ' . $article->title, $history->message);
        $history->delete();

        $this->json('POST', '/articles/' . $article->id . '/restore');
        $history = History::first();
        $this->assertNotNull($history);
        $this->assertEquals($article->id, $history->model_id);
        $this->assertEquals('Restored Article ' . $article->title, $history->message);
    }

    public function testAuthed()
    {
        $user = User::first();
        $this->assertNotNull($user);

        $content = ['title' => 'voluptas ut rem'];
        $this->actingAs($user)->json('POST', '/articles', $content)->assertJson($content);

        $article = Article::first();
        $this->assertNotNull($article);
        $histories = $article->histories;
        $this->assertNotNull($histories);
        $this->assertEquals(1, count($histories));
        $history = $histories[0];
        $this->assertTrue($history->hasUser());
        $this->assertNotNull($history->user());
        $this->assertEquals($user->toJson(), $history->user()->toJson());
        $this->assertEquals($article->makeHidden('histories')->toJson(), $history->model()->toJson());
        
        $operations = $user->operations;
        $this->assertNotNull($operations);
        $this->assertEquals(1, count($operations));
        $operation = $operations[0];
        $this->assertEquals($history->toJson(), $operation->toJson());
    }

    public function testAnonymous()
    {
        $content = ['title' => 'quae et est'];
        $this->json('POST', '/articles', $content)->assertJson($content);

        $article = Article::first();
        $this->assertNotNull($article);
        $histories = $article->histories;
        $this->assertNotNull($histories);
        $this->assertEquals(1, count($histories));
        $history = $histories[0];
        $this->assertNotTrue($history->hasUser());
        $this->assertNull($history->user());
    }

    public function testCustomGuard()
    {
        $user = User::first();
        $this->assertNotNull($user);

        $content = ['title' => 'voluptas ut rem'];
        $this->actingAsAdmin($user)->json('POST', '/articles', $content)->assertJson($content);

        $article = Article::first();
        $this->assertNotNull($article);
        $histories = $article->histories;
        $this->assertNotNull($histories);
        $this->assertEquals(1, count($histories));
        $history = $histories[0];
        $this->assertTrue($history->hasUser());
        $this->assertNotNull($history->user());
        $this->assertEquals($user->toJson(), $history->user()->toJson());
        $this->assertEquals($article->makeHidden('histories')->toJson(), $history->model()->toJson());
        
        $operations = $user->operations;
        $this->assertNotNull($operations);
        $this->assertEquals(1, count($operations));
        $operation = $operations[0];
        $this->assertEquals($history->toJson(), $operation->toJson());
    }

    public function testCustomEvent()
    {
        Article::create(['title' => 'maxime fugit saepe']);
        $article = Article::first();
        $this->assertNotNull($article);
        $this->json('GET', '/articles/' . $article->id);
        $history = History::skip(1)->first();
        $this->assertNotNull($history);
        $this->assertEquals($article->id, $history->model_id);
        $this->assertEquals('Query Article ' . $article->title, $history->message);
        $this->assertEquals([$article->id], $history->meta);
    }

    private function actingAsAdmin($admin) {
        $defaultGuard = config('auth.defaults.guard');
        $this->actingAs($admin, 'admin');
        Auth::shouldUse($defaultGuard);
        return $this;
    }
}