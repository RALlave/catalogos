<?php

use App\Support\RichText;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Columns written with the rich text editor.
     *
     * @var array<string, string>
     */
    private array $columns = [
        'heroes' => 'text',
        'products' => 'description',
        'categories' => 'description',
        'stores' => 'description',
    ];

    /**
     * The panel now saves these texts as HTML. The limit counts visible
     * characters, so the hero text no longer fits in a 255 column once the
     * tags are added. Existing plain text becomes HTML keeping its line
     * breaks: a blank line starts a paragraph, a single one is a <br>.
     */
    public function up(): void
    {
        Schema::table('heroes', function (Blueprint $table) {
            $table->text('text')->nullable()->change();
        });

        foreach ($this->columns as $table => $column) {
            DB::table($table)->whereNotNull($column)->orderBy('id')->each(function (object $row) use ($table, $column) {
                DB::table($table)->where('id', $row->id)->update([$column => $this->toHtml($row->{$column})]);
            });
        }
    }

    public function down(): void
    {
        foreach ($this->columns as $table => $column) {
            DB::table($table)->whereNotNull($column)->orderBy('id')->each(function (object $row) use ($table, $column) {
                $text = RichText::toText($row->{$column});

                DB::table($table)->where('id', $row->id)->update([
                    $column => $table === 'heroes' && $text !== null ? mb_substr($text, 0, 255) : $text,
                ]);
            });
        }

        Schema::table('heroes', function (Blueprint $table) {
            $table->string('text', 255)->nullable()->change();
        });
    }

    private function toHtml(string $text): ?string
    {
        $text = trim(str_replace(["\r\n", "\r"], "\n", $text));

        $paragraphs = array_filter(preg_split('/\n\s*\n/', $text), fn (string $p) => trim($p) !== '');

        $html = implode('', array_map(
            fn (string $p) => '<p>'.nl2br(e(trim($p)), false).'</p>',
            $paragraphs,
        ));

        return RichText::sanitize(str_replace("\n", '', $html));
    }
};
