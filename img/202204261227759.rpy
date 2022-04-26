

init -5 python:
    def color_int_to_hex(color):
        return ''.join(["%02x" % e for e in color])

    def color_float_to_hex(color):
        return ''.join(["%02x" % (e * 255) for e in color])

    def color_hex_to_int(color):
        if color[0] == "#":
            color = color[1:]
        if len(color) == 6:
            color += 'ff'
        elif len(color) == 4:
            color = color[0] + color[0] + color[1] + color[1] + color[2] + color[2] + color[3] + color[3]
        elif len(color) == 3:
            color = color[0] + color[0] + color[1] + color[1] + color[2] + color[2]
            color += 'ff'
        
        return tuple(int(color[i:i+2], 16) for i in (0, 2, 4, 6))

    def color_hex_to_float(color):
        color = color_hex_to_int(color)
        return tuple(i / 255.0 for i in color)

    renpy.register_shader("darkpy.gradient", 
        variables="""
            uniform vec4 u_color_left;
            uniform vec4 u_color_right;
            uniform float u_angle;
            uniform float u_mul;
            
            uniform float u_lod_bias;
            uniform sampler2D tex0;
            uniform vec2 u_model_size;
            
            attribute vec2 a_tex_coord;
            attribute vec4 a_position;
            
            varying vec2 v_tex_coord;
            varying float v_gradient;
        """, 
        vertex_300="""
            v_tex_coord = a_tex_coord;
            v_gradient = (a_position.x * cos(radians(u_angle)) + a_position.y * sin(radians(u_angle))) / (u_model_size.x * cos(radians(u_angle)) + u_model_size.y * sin(radians(u_angle)));
        """, 
        fragment_300="""
            vec4 mainTex = texture2D(tex0, v_tex_coord.st, u_lod_bias);
            mainTex.x = min(mainTex.x + mainTex.y + mainTex.z, 1.0);
            gl_FragColor = vec4(
                mainTex.x * mix(u_color_left.xyz, u_color_right.xyz, v_gradient) * u_mul,
                mainTex.x * u_color_left.w
            );
        """
    )
# Decompiled by unrpyc: https://github.com/CensoredUsername/unrpyc
