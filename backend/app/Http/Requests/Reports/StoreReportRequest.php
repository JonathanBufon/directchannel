<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Enums\ReportReason;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reported_user_id' => ['required', 'integer', Rule::exists('users', 'id'), Rule::notIn([$this->user()->id])],
            'reason' => ['required', Rule::enum(ReportReason::class)],
            'narrative' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'reported_user_id.integer' => 'Informe uma conta válida.',
            'reported_user_id.exists' => 'A conta informada não existe.',
            'reported_user_id.not_in' => 'Você não pode denunciar sua própria conta.',
            'reason.enum' => 'Selecione um dos motivos de denúncia disponíveis.',
            'narrative.string' => 'A descrição deve ser um texto.',
            'narrative.max' => 'A descrição deve ter no máximo 2000 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return ['reported_user_id' => 'usuário denunciado', 'reason' => 'motivo'];
    }
}
