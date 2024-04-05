import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

interface TeachersProps {
    auth: any;
    teachers: any[];
}

const Teachers: React.FC<TeachersProps> = ({ auth, teachers }) => {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800  leading-tight">Instrutores</h2>}
        >
            <div className='flex flex-wrap pb-6'>
                {teachers.map((teacher: any) => (
                    <div className="max-w-2xl mx-4 sm:max-w-sm md:max-w-sm lg:max-w-sm xl:max-w-sm sm:mx-auto md:mx-auto lg:mx-auto xl:mx-auto mt-16 bg-white shadow-xl rounded-lg text-gray-900">
                        <div className="rounded-t-lg h-32 overflow-hidden">
                            <img className="object-cover object-top w-full" src='https://images.unsplash.com/photo-1549880338-65ddcdfd017b?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=400&fit=max&ixid=eyJhcHBfaWQiOjE0NTg5fQ' alt='Mountain' />
                        </div>
                        <div className="mx-auto w-32 h-32 relative -mt-16 border-4 border-white rounded-full overflow-hidden">
                            <img className="object-cover object-center h-32" src='https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-1.2.1&q=80&fm=jpg&crop=entropy&cs=tinysrgb&w=400&fit=max&ixid=eyJhcHBfaWQiOjE0NTg5fQ' alt='Woman looking front' />
                        </div>
                        <div className="text-center mt-2 p-6">
                            <h2 className="font-semibold">{teacher.name}</h2>
                            <p className="font-semibold text-gray-500">{teacher.skills}</p>
                            <p className='text-gray-500'>{teacher.bio}</p>
                        </div>
                    </div>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}

export default Teachers;
