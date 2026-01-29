<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\NfeEntradas\NfeEntradaResource;
use App\Filament\Tables\Columns\TagBadgesColumn;
use App\Models\CategoryTag;
use App\Models\Issuer;
use App\Models\NotaFiscalEletronica;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TagBadgesColumnTest extends TestCase
{
    use RefreshDatabase;

    public function test_render_sem_etiquetas_exibe_placeholder(): void
    {
        $issuer = $this->createIssuer();
        $nfe = NotaFiscalEletronica::create([
            'issuer_id' => $issuer->id,
        ]);

        $column = TagBadgesColumn::make('tagged')->label('Etiqueta');

        $html = view('filament.tables.columns.tag-badges-column', [
            'record' => $nfe->load('tagged.tag'),
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('—', $html);
    }

    public function test_render_com_etiquetas_exibe_badge_e_tooltip_com_nome_e_valor(): void
    {
        $issuer = $this->createIssuer();
        $category = CategoryTag::create([
            'issuer_id' => $issuer->id,
            'name' => 'Categoria',
            'is_enable' => true,
            'order' => 1,
            'color' => '22c55e',
        ]);

        $tag = Tag::create([
            'slug' => Str::slug('Manutenção De Moldes'),
            'name' => 'Manutenção De Moldes',
            'count' => 0,
            'tag_group_id' => null,
            'category_id' => $category->id,
            'code' => null,
            'is_enable' => true,
            'issuer_id' => $issuer->id,
        ]);

        $nfe = NotaFiscalEletronica::create([
            'issuer_id' => $issuer->id,
        ]);
        $nfe->tag($tag, 509.10);
        $nfe->load('tagged.tag.category');

        $column = TagBadgesColumn::make('tagged')->label('Etiqueta');

        $html = view('filament.tables.columns.tag-badges-column', [
            'record' => $nfe,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString(getLabelTag('Manutenção De Moldes'), $html);
        $this->assertStringContainsString('x-tooltip', $html);
        $this->assertStringContainsString('Manutenção De Moldes - R$ 509,10', $html);
    }

    public function test_render_com_muitas_etiquetas_exibe_indicador_mais(): void
    {
        $issuer = $this->createIssuer();
        $category = CategoryTag::create([
            'issuer_id' => $issuer->id,
            'name' => 'Categoria',
            'is_enable' => true,
            'order' => 1,
            'color' => '22c55e',
        ]);

        $nfe = NotaFiscalEletronica::create([
            'issuer_id' => $issuer->id,
        ]);

        foreach (['Primeira Etiqueta', 'Segunda Etiqueta', 'Terceira Etiqueta'] as $idx => $name) {
            $tag = Tag::create([
                'slug' => Str::slug($name),
                'name' => $name,
                'count' => 0,
                'tag_group_id' => null,
                'category_id' => $category->id,
                'code' => null,
                'is_enable' => true,
                'issuer_id' => $issuer->id,
            ]);

            $nfe->tag($tag, 10 + $idx);
        }

        $nfe->load('tagged.tag.category');

        $column = TagBadgesColumn::make('tagged')
            ->label('Etiqueta')
            ->maxVisible(2);

        $html = view('filament.tables.columns.tag-badges-column', [
            'record' => $nfe,
            'column' => $column,
        ])->render();

        $this->assertStringContainsString('+1 mais', $html);
        $this->assertStringContainsString('Terceira Etiqueta', $html);
    }

    public function test_eager_loading_na_listagem_nao_gera_consultas_n_mais_1(): void
    {
        $issuer = $this->createIssuer();
        $category = CategoryTag::create([
            'issuer_id' => $issuer->id,
            'name' => 'Categoria',
            'is_enable' => true,
            'order' => 1,
            'color' => '22c55e',
        ]);

        $tags = collect(['Primeira Etiqueta', 'Segunda Etiqueta', 'Terceira Etiqueta'])
            ->map(function (string $name) use ($issuer, $category): Tag {
                return Tag::create([
                    'slug' => Str::slug($name),
                    'name' => $name,
                    'count' => 0,
                    'tag_group_id' => null,
                    'category_id' => $category->id,
                    'code' => null,
                    'is_enable' => true,
                    'issuer_id' => $issuer->id,
                ]);
            });

        for ($i = 0; $i < 10; $i++) {
            $nfe = NotaFiscalEletronica::create([
                'issuer_id' => $issuer->id,
            ]);

            foreach ($tags as $idx => $tag) {
                $nfe->tag($tag, 10 + $idx);
            }
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $nfes = NfeEntradaResource::getEloquentQuery()
            ->where('issuer_id', $issuer->id)
            ->get();

        foreach ($nfes as $nfe) {
            foreach ($nfe->tagged as $tagged) {
                $tagged->tag?->category?->color;
            }
        }

        $this->assertLessThanOrEqual(6, count(DB::getQueryLog()));
    }

    private function createIssuer(): Issuer
    {
        $user = \App\Models\User::factory()->create();

        return Issuer::create([
            'user_id' => $user->id,
            'cnpj' => '12345678000199',
            'razao_social' => 'Empresa Teste',
            'ambiente' => 2,
        ]);
    }
}
