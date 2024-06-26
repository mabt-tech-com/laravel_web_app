<?php

namespace App\Http\Controllers;

use App\Models\File;

class FilesController extends Controller
{
    public function upload_file()
    {
        try {
            request()->validate([
                'type' => 'required|integer|in:1,2',
                'file' => 'required_if:type,1',
                'url' => 'required_if:type,2',
            ]);

            if (request('type') === File::TYPE_INTERNAL) {
                $file_name = auth()->user()->id . request()->file('file')->getClientOriginalName();
                $image_path = request()->file('file')->storeAs('public/files', $file_name);
                $url = url('storage/files/' . $file_name);
            } elseif (request('type') === File::TYPE_EXTERNAL) {
                $file_name = null;
                $url = request('url');
            }

            $file = File::create([
                'user_id' => auth()->user()->id,
                'type' => request('type'),
                'file_name' => $file_name,
                'url' => $url,
            ]);

            insert_in_history_table('created', $file->id, $file->getTable());

            return response()->json([
                'file_id' => $file->id,
                'url' => $file->url,
                'message' => 'File uploaded successfully.',
            ]);
        } catch (\Throwable $th) {
            report($th);
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }

    public function delete_file($id)
    {
        try {
            $file = File::findOrFail($id);

            if (\Storage::exists('public/files/' . $file->file_name)) {
                \Storage::delete('public/files/' . $file->file_name);
                $file->delete();

                insert_in_history_table('deleted', $file->id, $file->getTable());

                return response()->json(['message' => 'File deleted successfully.']);
            }
            return response()->json(['message' => 'File does not exists.']);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }
    }
}
