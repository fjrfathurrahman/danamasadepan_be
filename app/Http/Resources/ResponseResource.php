<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ResponseResource extends JsonResource
{
    private int $status;
    private string $message;
    private mixed $data;

    /**
     * Membuat instance dari ResponseResource
     *
     * @param array{0: int, 1: string, 2?: mixed} $resource
     */
    public function __construct(array $resource)
    {
        $this->status = $resource[0];
        $this->message = $resource[1];
        $this->data = $resource[2] ?? null;

        parent::__construct($resource);
    }

    /**
     * Mengubah objek menjadi array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            // Jika status >= 400, gunakan "errors", selain itu gunakan "result"
            $this->status >= 400 ? 'errors' : 'result' => $this->whenNotNull($this->data)
        ];
    }

    /**
     * Membuat HTTP response
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json($this->toArray($request), $this->status);
    }

    /**
     * Create a success response
     *
     * @param string $message
     * @param mixed|null $data
     * @return static
     */
    public static function success(string $message, mixed $data = null): self
    {
        return new static([200, $message, $data]);
    }

    /**
     * Create a not found response
     *
     * @param string $message
     * @return static
     */
    public static function notFound(string $message = 'Data Tidak Ditemukan'): self
    {
        return new static([404, $message, null]);
    }

    /**
     * Create an error response
     *
     * @param string $message
     * @param int $status
     * @return static
     */
    public static function error(string $message, int $status = 500): self
    {
        return new static([$status, $message, null]);
    }
}

