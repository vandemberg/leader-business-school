import React from "react";
import images from "../../utils/images";
import { router, Link } from "@inertiajs/react";

interface ThumbProps {
    course: {
        id: number;
        title: string;
        description: string;
        thumbnail: string;
        icon: string;
    }
}

const Thumb: React.FC<ThumbProps> = ({ course }) => {
    return (
        <div className="sm:rounded-lg flex items-center justify-between flex-col w-1/4 border-2 border-solid border-gray-600 hover:border-gray-400 flex-grow max-w-[320px] flex-wrap">
            <div className="w-full px-2 text-left">
                <div className="w-full flex items-center">
                    <img src={images[course.thumbnail as keyof typeof images]} alt={course.title} />
                </div>

                <div className="w-full flex items-center flex-col p-4">
                    <h2 className="text-gray-900my-4 font-bold">
                        {course.title}
                    </h2>

                    <p className="text-gray-500 text-justify mt-5">
                        {course.description.substring(0, 100) + '...'}
                    </p>
                </div>

                <div className="w-full flex items-center justify-center my-4">
                    <Link href={`/courses/${course.id}/watch`} className="bg-blue-500 text-white font-bold py-2 px-4 rounded-full w-full text-center">
                        Assistir o curso
                    </Link>
                </div>
            </div>
        </div>
    )
}

export { Thumb };
