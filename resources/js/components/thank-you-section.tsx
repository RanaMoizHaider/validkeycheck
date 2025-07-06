import React from 'react';
import { ExternalLink, Users } from 'lucide-react';

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
    role: 'Creator & Maintainer'
  },
];

interface ThankYouSectionProps {
  className?: string;
}

export default function ThankYouSection({ className }: ThankYouSectionProps) {
  return (
    <div className={`mt-16 ${className}`}>
      <div className="text-center mb-8">
        <h2 className="text-2xl font-bold text-black dark:text-white mb-2">Credits</h2>
        <p className="text-gray-600 dark:text-gray-400">
          Built with amazing open-source technologies and contributions
        </p>
      </div>

      {/* Technologies */}
      <div className="mb-8">
        <h3 className="text-lg font-semibold text-black dark:text-white mb-4">Technologies</h3>
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
          {technologies.map((tech) => (
            <a
              key={tech.name}
              href={tech.url}
              target="_blank"
              rel="noopener noreferrer"
              className="group flex items-center gap-2 p-3 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-gray-300 dark:hover:border-gray-700 transition-colors text-sm justify-center"
            >
              <span className="font-medium text-black dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">
                {tech.name}
              </span>
            </a>
          ))}
        </div>
      </div>

      {/* Contributors */}
      <div className="mb-8">
        <h3 className="text-lg font-semibold text-black dark:text-white mb-4">
          Contributors
        </h3>
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
          {contributors.map((contributor) => (
            <a
              key={contributor.username}
              href={contributor.url}
              target="_blank"
              rel="noopener noreferrer"
              className="group flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-800 rounded-lg hover:border-gray-300 dark:hover:border-gray-700 transition-colors"
            >
              <img
                src={contributor.avatar}
                alt={contributor.name}
                className="w-10 h-10 rounded-full"
              />
              <div className="flex-1 min-w-0">
                <div className="font-medium text-black dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors truncate">
                  {contributor.name}
                </div>
                <div className="text-xs text-gray-600 dark:text-gray-400 truncate">
                  {contributor.role}
                </div>
              </div>
            </a>
          ))}
        </div>
      </div>

      {/* Thank you message */}
      <div className="text-center">
        <div className="rounded-lg p-4 border border-gray-200 dark:border-gray-800">
          <p className="text-sm text-gray-700 dark:text-gray-300">
            Thank you to the open-source community for making this project possible.
          </p>
        </div>
      </div>
    </div>
  );
} 