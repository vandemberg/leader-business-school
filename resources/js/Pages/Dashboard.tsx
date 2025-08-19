import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";
import { Head } from "@inertiajs/react";
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
    platform: PlatformProp;
}

interface PlatformProp {
    id: number;
    name: string;
    slug: string;
}

const Dashboard: React.FC<DashboardProps> = ({ auth, courses, platform }) => {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="font-semibold text-xl text-gray-800  leading-tight">
                    {platform.name}
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
};

export default Dashboard;
