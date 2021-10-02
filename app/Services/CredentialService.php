<?php

namespace App\Services;

use App\Models\Email;
use App\Models\Password;
use App\Models\PhoneNumber;
use App\Models\QuestionAnswer;
use App\Models\SecurityCode;
use App\Models\Username;

use Illuminate\Support\Facades\Crypt;

class CredentialService
{
    public function email_crud($option, $credential_id, $body = null)
    {
        $ending = explode('@', $body, 2)[1];

        if ($option === "create") {
            Email::create([
                'slot_id' => $credential_id,
                'email' => Crypt::encryptString($body),
                'opening' => substr($body, 0, 2),
                'ending' => '@' . $ending,
                'char_count' => strlen($body) - 2 - strlen($ending) + 1,
            ]);
        }

        if ($option === "update") {
            $email = Email::where('slot_id', $credential_id)->first();

            $email->email = Crypt::encryptString($body);
            $email->opening = substr($body, 0, 2);
            $email->ending = '@' . $ending;
            $email->char_count = strlen($body) - 2 - strlen($ending) + 1;
        }

        if ($option === "delete") {
            $email = Email::where('slot_id', $credential_id)->first();

            $email->delete();
        }
    }

    public function password_crud($option, $credential_id, $body = null)
    {
        if ($option === "create") {
            Password::create([
                'slot_id' => $credential_id,
                'password' => Crypt::encryptString($body),
                'char_count' => strlen($body)
            ]);
        }

        if ($option === "update") {
            $password = Password::where('slot_id', $credential_id)->first();

            $password->password = Crypt::encryptString($body);
            $password->char_count = strlen($body);
            $password->save();
        }

        if ($option === "delete") {
            $password = Password::where('slot_id', $credential_id)->first();

            $password->delete();
        }
    }

    public function phone_number_crud($option, $credential_id, $body = null)
    {
        if ($option === "create") {
            PhoneNumber::create([
                'slot_id' => $credential_id,
                'phone_number' => Crypt::encryptString($body),
                'opening' => substr($body, 0, 3),
                'char_count' => strlen($body) - 5,
                'ending' => substr($body, -2)
            ]);
        }

        if ($option === "update") {
            $phone_number = PhoneNumber::where('slot_id', $credential_id)->first();

            $phone_number->phone_number = Crypt::encryptString($body);
            $phone_number->opening = substr($body, 0, 3);
            $phone_number->char_count = strlen($body) - 5;
            $phone_number->ending = substr($body, -2);

            $phone_number->save();
        }

        if ($option === "delete") {
            $phone_number = PhoneNumber::where('slot_id', $credential_id)->first();

            $phone_number->delete();
        }
    }

    public function question_answer_crud($option, $credential_id, $body = null)
    {
        if ($option === "create") {
            QuestionAnswer::create([
                'slot_id' => $credential_id,
                'security_question' => Crypt::encryptString($body['question']),
                'security_answer' => Crypt::encryptString($body['answer']),
            ]);
        }

        if ($option === "update") {
            $question_answer = QuestionAnswer::where('slot_id', $credential_id)->first();

            $question_answer->security_question = Crypt::encryptString($body['question']);
            $question_answer->security_answer = Crypt::encryptString($body['answer']);

            $question_answer->save();
        }

        if ($option === "delete") {
            $question_answer = QuestionAnswer::where('slot_id', $credential_id)->first();

            $question_answer->delete();
        }
    }

    public function username_crud($option, $credential_id, $body = null)
    {
        if ($option === "create") {
            Username::create([
                'slot_id' => $credential_id,
                'username' => Crypt::encryptString($body),
                'char_count' => strlen($body),
            ]);
        }

        if ($option === "update") {
            $username = Username::where('slot_id', $credential_id)->first();

            $username->username = Crypt::encryptString($body);
            $username->char_count = strlen($body);

            $username->save();
        }

        if ($option === "delete") {
            $username = Username::where('slot_id', $credential_id)->first();

            $username->delete();
        }
    }

    public function security_code_crud($option, $credential_id, $body)
    {
        if ($option === "create") {
            SecurityCode::create([
                'slot_id' => $credential_id,
                'unique_code' =>
                isset($body['unique_code'])
                    ?
                    Crypt::encryptString($body['unique_code'])
                    :
                    null,
                'unique_code_length' =>
                isset($body['unique_code'])
                    ?
                    strlen($body['unique_code'])
                    :
                    null,
                'multiple_codes' =>
                isset($body['multiple_codes'])
                    ?
                    Crypt::encryptString(implode('<@>', $body['multiple_codes']))
                    :
                    null,
                'multiple_codes_length' =>
                isset($body['multiple_codes'])
                    ?
                    count($body['multiple_codes'])
                    :
                    null,
                'crypto_codes' =>
                isset($body['crypto_codes'])
                    ?
                    Crypt::encryptString(implode('<@>', $body['crypto_codes']))
                    :
                    null,
                'crypto_codes_length' =>
                isset($body['crypto_codes'])
                    ?
                    count($body['crypto_codes'])
                    :
                    null
            ]);
        }

        if ($option === "update or delete") {
            $codes = SecurityCode::where('slot_id', $credential_id)->first();

            if (is_null($codes)) {
                $this->security_code_crud('create', $credential_id, $body);

                return;
            }

            $codes->unique_code = isset($body['unique_code']) ? Crypt::encryptString($body['unique_code']) : null;
            $codes->multiple_codes = isset($body['multiple_codes']) ? Crypt::encryptString(implode('<@>', $body['multiple_codes'])) : null;
            $codes->crypto_codes = isset($body['crypto_codes']) ? Crypt::encryptString(implode('<@>', $body['crypto_codes'])) : null;

            $codes->unique_code_length = isset($body['unique_code']) ? strlen($body['unique_code']) : null;
            $codes->multiple_codes_length = isset($body['multiple_code']) ? count($body['multiple_codes']) : null;
            $codes->crypto_codes_length = isset($body['crypto_codes']) ? count($body['crypto_codes']) : null;

            if (is_null($codes->unique_code) && is_null($codes->multiple_codes) && is_null($codes->crypto_codes)) {
                $codes->delete();
            } else {
                $codes->save();
            }
        }
    }
}
