<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

namespace Modules\Documents\Tests\Feature;

use Illuminate\Support\Collection;
use Modules\Billable\Models\Billable;
use Modules\Billable\Models\BillableProduct;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentSigner;
use Tests\TestCase;

class DocumentContentTest extends TestCase
{
    public function test_it_adds_products_html(): void
    {
        $documentFactory = Document::factory([
            'content' => '<div class="products-section">
                <span>
                    Products will be displayed in this section on document preview and PDF.
                </span>
            </div>',
        ]);

        $billable = Billable::factory()
            ->withBillableable($documentFactory)
            ->has(BillableProduct::factory(), 'products')
            ->create();

        $expected = view('billable::products.table')->with([
            'billable' => $billable,
        ])->render();

        $this->assertStringContainsString($expected, $billable->billableable->content->withProducts()->html());
    }

    public function test_it_adds_signatures_html(): void
    {
        $signaturesMarkup = '<div class="signatures-section">
            <span>
                On document PDF, the signatures will be displayed in this section.
            </span>
        </div>';

        $document = Document::factory()
            ->has(DocumentSigner::factory()->signed()->count(2), 'signers')
            ->create([
                'content' => $content = '<div>Content</div>'.$signaturesMarkup.'<div>Content</div>',
            ]);

        $expected = str_replace($signaturesMarkup, view('documents::signatures')->with([
            'document' => $document,
        ])->render(), $content);

        $this->assertStringContainsString($expected, $document->content->withSignatures()->html());
    }

    public function test_it_adds_signatures_html_when_at_least_one_signed_but_signatures_markup_is_missing(): void
    {
        $document = Document::factory()
            ->has(DocumentSigner::factory()->signed(), 'signers')
            ->create([
                'content' => '<div>Content</div>',
            ]);

        $expected = view('documents::signatures')->with([
            'document' => $document,
        ])->render();

        $this->assertStringContainsString($expected, $document->content->withSignatures()->html());
    }

    public function test_it_does_not_add_products_html_markup_when_document_missing_products(): void
    {
        $document = Document::factory()->create([
            'content' => '<div>Content</div><div class="products-section">
                <span>
                    Products will be displayed in this section on document preview and PDF.
                </span>
            </div>',
        ]);

        $this->assertSame('<div>Content</div>', $document->content->withProducts()->html());
    }

    public function test_it_can_determine_the_used_google_fonts(): void
    {
        $document = Document::factory()->create([
            'content' => '<div style="font-family: Alegreya, serif;">Content</div>',
        ]);

        $fonts = $document->content->usedGoogleFonts();

        $this->assertInstanceOf(Collection::class, $fonts);
        $this->assertNotEmpty($fonts);
        $this->assertcount(1, $fonts);
        $this->assertSame('Alegreya, serif', $fonts[0]['font-family']);
        $this->assertSame('google', $fonts[0]['provider']);
        $this->assertSame('Alegreya', $fonts[0]['name']);
    }

    public function test_it_can_determine_the_used_fonts_with_quotes(): void
    {
        $document = Document::factory()->create([
            'content' => '<div style="font-family: \'Exo 2\', sans-serif;">Content</div>',
        ]);

        $fonts = $document->content->usedGoogleFonts();

        $this->assertInstanceOf(Collection::class, $fonts);
        $this->assertNotEmpty($fonts);
        $this->assertcount(1, $fonts);
        $this->assertSame('Exo 2, sans-serif', $fonts[0]['font-family']);
        $this->assertSame('Exo 2', $fonts[0]['name']);
    }
}
