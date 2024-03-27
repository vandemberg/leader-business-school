import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { PageProps } from '@/types';
import React from 'react';

interface CourseProps {
    videos: any[],
    modules: any[],
    currentVideo: {
        id: number;
        title: string;
        description: string;
        url: string;
    },
    currentModule: {
        id: number;
        title: string;
        description: string;
    },
    course: {
        id: number;
        title: string;
        description: string;
        thumbnail: string;
        icon: string;
    },
    auth: any
}

const Course: React.FC<CourseProps> = ({ auth, course, currentModule, currentVideo, videos, modules }) => {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className='flex items-center text-gray-800'>
                    <i className='bx bx-left-arrow-alt cursor-pointer' style={{ fontSize: '22px' }}></i>
                    <h2 className="font-semibold text-xl  leading-tight flex items-center">
                        {course.title}
                    </h2>
                </div>
            }
        >
            <p className="m-6 mx-6 text-xltext-bold text-gray-800">
                {currentVideo.title}
            </p>

            <div className="flex flex-1 bg-whitem-6 p-6 rounded-smgap-6 max-h-[600px] gap-2">
                <div className='flex w-3/4 h-[560px]'>
                    <iframe className="w-full h-full" src={currentVideo.url}>
                    </iframe>
                </div>

                <div className='w-1/4 bg-gray-100rounded-sm border-gray-800 border-solid border-x border-y overflow-scroll max-h-[600px]'>
                    <div className='flex flex-col items-center gap-2 p-2'>
                        <span className='text-gray-800'> Conteúdos </span>

                        {[1, 2, 3, 4, 5, 6, 7, 8].map((index) => (
                            <div key={index} className='w-full h-1/6 bg-gray-200flex rounded-sm p-2 flex-col cursor-pointer hover:border-x-2 hover:border-y-2 border-gray-800'>
                                <div className='flex items-center gap-4'>
                                    <i className='bx bxs-videos'></i>
                                    <span className='font-bold'>O que faz um bom líder?</span>
                                </div>
                                <span className='w-full text-right text-sm pl-4 text-gray-500 mt-2'>9m e 10 segundos</span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            <p className='pl-12'>
                {currentVideo.description}
            </p>
        </AuthenticatedLayout>
    );
}

export default Course;
