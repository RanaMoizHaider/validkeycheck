const technologies = [
    { name: 'Laravel', url: 'https://laravel.com' },
    { name: 'Inertia.js', url: 'https://inertiajs.com' },
    { name: 'React', url: 'https://react.dev' },
    { name: 'Prism PHP', url: 'https://prismphp.com' },
    { name: 'Sushi', url: 'https://usesushi.dev' },
    { name: 'Tailwind CSS', url: 'https://tailwindcss.com' },
    { name: 'Radix UI', url: 'https://radix-ui.com' },
    { name: 'Lucide React', url: 'https://lucide.dev' },
    { name: 'TypeScript', url: 'https://typescriptlang.org' },
    { name: 'Vite', url: 'https://vitejs.dev' },
    { name: 'Pest PHP', url: 'https://pestphp.com' },
    { name: 'Ziggy', url: 'https://github.com/tighten/ziggy' },
];

const contributors = [
    {
        name: 'Rana Moiz Haider',
        username: 'ranamoizhaider',
        avatar: 'https://github.com/ranamoizhaider.png',
        url: 'https://github.com/ranamoizhaider',
        role: 'Creator & Maintainer',
    },
];

interface ThankYouSectionProps {
    className?: string;
}

export default function ThankYouSection({ className }: ThankYouSectionProps) {
    return (
        <div className={`mt-16 ${className}`}>
            <div className="mb-8 text-center">
                <h2 className="mb-2 text-2xl font-bold text-black dark:text-white">Credits</h2>
                <p className="text-gray-600 dark:text-gray-400">Built with amazing open-source technologies and contributions</p>
            </div>

            {/* Technologies */}
            <div className="mb-8">
                <h3 className="mb-4 text-lg font-semibold text-black dark:text-white">Technologies</h3>
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    {technologies.map((tech) => (
                        <a
                            key={tech.name}
                            href={tech.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="group flex items-center justify-center gap-2 rounded-lg border border-gray-200 p-3 text-sm transition-colors hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700"
                        >
                            <span className="truncate font-medium text-black transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                {tech.name}
                            </span>
                        </a>
                    ))}
                </div>
            </div>

            {/* Contributors */}
            <div className="mb-8">
                <h3 className="mb-4 text-lg font-semibold text-black dark:text-white">Contributors</h3>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
                    {contributors.map((contributor) => (
                        <a
                            key={contributor.username}
                            href={contributor.url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="group flex items-center gap-3 rounded-lg border border-gray-200 p-3 transition-colors hover:border-gray-300 dark:border-gray-800 dark:hover:border-gray-700"
                        >
                            <img src={contributor.avatar} alt={contributor.name} className="h-10 w-10 rounded-full" />
                            <div className="min-w-0 flex-1">
                                <div className="truncate font-medium text-black transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                    {contributor.name}
                                </div>
                                <div className="truncate text-xs text-gray-600 dark:text-gray-400">{contributor.role}</div>
                            </div>
                        </a>
                    ))}
                </div>
            </div>

            {/* Thank you message */}
            <div className="text-center">
                <div className="rounded-lg border border-gray-200 p-4 dark:border-gray-800">
                    <p className="text-sm text-gray-700 dark:text-gray-300">
                        Thank you to the open-source community for making this project possible.
                    </p>
                </div>
            </div>
        </div>
    );
}
