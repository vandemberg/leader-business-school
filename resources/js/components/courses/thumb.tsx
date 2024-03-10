function Thumb() {
    return (
        <a href="/courses/maestria-lideranca" className="p-6 bg-white dark:bg-gray-800 overflow-hidden sm:rounded-lg flex items-center justify-center flex-col w-1/4 border-2 border-solid border-gray-600 hover:border-gray-400 cursor-pointer">
            <div className="w-full px-2  text-left">
                <div className="bg-gray-900 dark:bg-gray-100 rounded-full flex items-center justify-center" style={{ width: 50, height: 50 }}>
                    <i className='text-2xl bx bxs-conversation dark:text-gray-900 text-gray-100 '></i>
                </div>

                <h2 className="text-gray-900 dark:text-gray-100 my-4 font-bold">
                    Maestria em Liderança e Gestão de Pessoas
                </h2>

                <p className="text-gray-500 dark:text-gray-400">
                    Aprenda a liderar e gerir pessoas com eficiência, consiga resultados e destaque-se no mercado de trabalho sendo um líder autêntico e eficaz.
                </p>
            </div>
        </a>
    )
}

export { Thumb };
