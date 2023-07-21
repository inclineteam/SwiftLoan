
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';

export default function OTPVerification({ token }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        otp: ''
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('otp-verification.store'));
    };

    return (
        <GuestLayout>
            <Head title="Reset Password" />

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="otp" value="OTP CODE" />

                    <TextInput
                        id="otp"
                        type="text"
                        name="otp"
                        value={data.otp}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => setData('otp', e.target.value)}
                    />

                    <InputError message={errors.otp} className="mt-2" />
                </div>

                <div className="flex items-center mt-4">
                    <div className='w-[50%] flex justify-start'>
                        <PrimaryButton className="self-start bg-[#48c563]" disabled={processing}>
                            Verify Otp
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </GuestLayout>
    );
}
