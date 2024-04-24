import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/components/InputError';
import PrimaryButton from '@/components/PrimaryButton';
import TextInput from '@/components/TextInput';
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function ForgotPassword({ status }: { status?: string }) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout>

            <div className="mb-4 text-sm text-gray-600 ">
                Esqueceu sua senha? Sem problemas. Apenas nos informe seu endereço de e-mail e enviaremos um link de com a opção de escolher uma nova.
            </div>

            {status && <div className="mb-4 font-medium text-sm text-green-600 ">{status}</div>}

            <form onSubmit={submit}>
                <TextInput
                    id="email"
                    type="email"
                    name="email"
                    value={data.email}
                    className="mt-1 block w-full"
                    isFocused={true}
                    onChange={(e) => setData('email', e.target.value)}
                />

                <InputError message={errors.email} className="mt-2" />

                <div className="flex items-center justify-end mt-4">
                    <PrimaryButton className="ms-4" disabled={processing}>
                        E-mail da conta de recuperação de senha
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
