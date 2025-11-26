import { ReactNode } from "react";

interface SelectProps {
  value?: string;
  placeholder?: string;
  onChange: (value: string) => void;
  className?: string;
  children: ReactNode;
  disabled?: boolean;
  required?: boolean;
  name?: string;
}

const FlexibleSelect: React.FC<SelectProps> = ({
  value = "",
  placeholder = "Select an option",
  onChange,
  className = "",
  children,
  disabled = false,
  required = false,
  name,
}) => {
  const handleChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
    onChange(e.target.value);
  };

  return (
    <select
      name={name}
      className={`h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 pr-11 text-sm shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 ${
        value
          ? "text-gray-800 dark:text-white/90"
          : "text-gray-400 dark:text-gray-400"
      } ${className}`}
      value={value}
      onChange={handleChange}
      disabled={disabled}
      required={required}
    >
      {/* Placeholder option */}
      <option
        value=""
        disabled
        className="text-gray-700 dark:bg-gray-900 dark:text-gray-400"
      >
        {placeholder}
      </option>
      {/* Render children (manually passed options) */}
      {children}
    </select>
  );
};

export default FlexibleSelect;