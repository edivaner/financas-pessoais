<?php
// app/Services/ImagemService.php
namespace App\Services;

use App\Models\Imagem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImagemService
{
    public function store(UploadedFile $file, string $tipo, string $userId): Imagem
    {
        $path = $file->store('imagens/' . strtolower($tipo), 'public');

        return Imagem::create([
            'user_id' => $userId,
            'caminho' => Storage::url($path),
            'tipo'    => $tipo,
        ]);
    }

    public function delete(string $id): void
    {
        $imagem = Imagem::findOrFail($id);
        $relativePath = str_replace('/storage/', '', $imagem->caminho);
        Storage::disk('public')->delete($relativePath);
        $imagem->delete();
    }
}
