const path = require('path');
const LiveReloadPlugin = require('webpack-livereload-plugin');
// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

const env = process.env.NODE_ENV;

const plugins = [
  // new MomentLocalesPlugin(),
  // new BundleAnalyzerPlugin(),
];

if (env !== 'production') {
  plugins.push(new LiveReloadPlugin());
}

module.exports = {
  resolve: {
    alias: {
      '@': path.resolve('resources/js'),
    },
  },
  module: {
    rules: [
      {
        test: /\.pug$/,
        loader: 'pug-plain-loader',
      },
    ],
  },
  plugins,
};
