import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { usePage, Head } from "@inertiajs/react";
import { Thumb } from "@/components/courses/thumb";

interface Course {
    id: number;
    title: string;
    description: string;
    thumbnail: string;
}

interface DashboardProps {
    auth: any;
    courses: Course[];
}

const Dashboard: React.FC<DashboardProps> = ({ auth, courses }) => {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800  leading-tight">
                    [Cursos]
                </h2>
            }
        >
            <Head title="Dashboard" />

            <p className="m-6 mx-6 text-xl text-gray-800 text-bold">
                Olá, {auth.user.name}!
            </p>

            <div className="m-6 p-6 flex gap-4 flex-wrap">
                {courses.map((course: any) => (
                    <Thumb key={course.id} course={course} />
                ))}
            </div>
        </AuthenticatedLayout>
    );
}

export default Dashboard;
