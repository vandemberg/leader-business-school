import React from "react";
import images from "../../utils/images";

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

    console.log(course.thumbnail);

    return (
        <a href={`/courses/${course.id}`} className="sm:rounded-lg flex items-center flex-col w-1/4 border-2 border-solid border-gray-600 hover:border-gray-400 cursor-pointer flex-grow max-w-[320px] flex-wrap">
            <div className="w-full h-1/2 px-2 text-left">
                <div className="w-full flex items-center">
                    <img src={images[course.thumbnail as keyof typeof images]} alt={course.title} />
                </div>

                <div className="w-full flex items-center flex-col h-1/2 p-4">
                    <h2 className="text-gray-900my-4 font-bold">
                        {course.title}
                    </h2>

                    <p className="text-gray-500 text-justify mt-5">
                        {course.description}
                    </p>
                </div>
            </div>
        </a >
    )
}

export { Thumb };
