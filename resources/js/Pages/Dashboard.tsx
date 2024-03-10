import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import { Thumb } from "@/components/courses/thumb";

export default function Dashboard({ auth }: PageProps) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">leader business school</h2>}
        >

            <p className="m-6 mx-6 text-xl text-gray-800 dark:text-white text-bold">
                Olá, {auth.user.name}!
            </p>

            <div className='m-6 p-6 flex gap-4'>
                <Thumb />
            </div>
        </AuthenticatedLayout>
    );
}
