import { cn } from '@/lib/utils';
import { Bot, Github, Heart } from 'lucide-react';

interface ValidatorFooterProps {
    className?: string;
    currentYear?: number;
}

export default function ValidatorFooter({ className, currentYear }: ValidatorFooterProps) {
    return (
        <div className={cn('border-t border-gray-200 px-4 py-3 dark:border-white/10', className)}>
            <div className="mx-auto max-w-5xl">
                {/* Mobile Layout */}
                <div className="flex flex-col items-center gap-2 text-center sm:hidden">
                    <div className="text-xs text-gray-600 dark:text-gray-400">© {currentYear} Valid Key Check</div>
                    <div className="flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                        <span>Built with</span>
                        <Heart className="h-3 w-3 text-red-500" fill="currentColor" />
                        <span>&</span>
                        <Bot className="h-3 w-3 text-blue-500" />
                        <a
                            href="https://github.com/ranamoizhaider/validkeycheck"
                            target="_blank"
                            rel="noopener noreferrer"
                            className="ml-1 flex items-center gap-1 transition-colors hover:text-black dark:hover:text-white"
                        >
                            <Github className="h-3 w-3" />
                            <span>Open Source</span>
                        </a>
                    </div>
                </div>

                {/* Desktop Layout */}
                <div className="hidden items-center justify-between sm:flex">
                    <div className="text-xs text-gray-600 dark:text-gray-400">© {currentYear} Valid Key Check</div>
                    <div className="flex items-center gap-4 text-xs text-gray-600 dark:text-gray-400">
                        <span>Validate your API keys securely</span>
                        <div className="flex items-center gap-1">
                            <span>Built with</span>
                            <Heart className="h-3 w-3 text-red-500" fill="currentColor" />
                            <span>&</span>
                            <Bot className="h-3 w-3 text-blue-500" />
                            <span>•</span>
                            <a
                                href="https://github.com/ranamoizhaider/validkeycheck"
                                target="_blank"
                                rel="noopener noreferrer"
                                className="flex items-center gap-1 transition-colors hover:text-black dark:hover:text-white"
                            >
                                <Github className="h-3 w-3" />
                                <span>Open Source</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
