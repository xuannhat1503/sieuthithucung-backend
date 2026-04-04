package com.sieuthithucung.service.impl;

import com.sieuthithucung.dto.WishlistDto;
import com.sieuthithucung.entity.WishlistEntity;
import com.sieuthithucung.exception.ResourceNotFoundException;
import com.sieuthithucung.mapper.WishlistMapper;
import com.sieuthithucung.repository.WishlistRepository;
import com.sieuthithucung.service.WishlistService;
import org.springframework.beans.BeanUtils;
import org.springframework.beans.BeanWrapper;
import org.springframework.beans.BeanWrapperImpl;
import org.springframework.stereotype.Service;

import java.beans.PropertyDescriptor;
import java.util.HashSet;
import java.util.List;
import java.util.Set;

@Service
public class WishlistServiceImpl implements WishlistService {

    private final WishlistRepository repository;

    public WishlistServiceImpl(WishlistRepository repository) {
        this.repository = repository;
    }

    @Override
    public WishlistDto create(WishlistDto dto) {
        return createInternal(dto);
    }

    @Override
    public WishlistDto update(Long id, WishlistDto dto) {
        return updateInternal(id, dto);
    }

    @Override
    public void delete(Long id) {
        if (!repository.existsById(id)) {
            throw new ResourceNotFoundException("Wishlist not found with id: " + id);
        }
        repository.deleteById(id);
    }

    @Override
    public WishlistDto findById(Long id) {
        return findByIdInternal(id);
    }

    @Override
    public List<WishlistDto> getAll() {
        return getAllInternal();
    }

    private WishlistDto createInternal(WishlistDto dto) {
        WishlistEntity entity = WishlistMapper.mapToWishlistEntity(dto);
        WishlistEntity saved = repository.save(entity);
        return WishlistMapper.mapToWishlistDto(saved);
    }

    private WishlistDto updateInternal(Long id, WishlistDto dto) {
        WishlistEntity existing = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Wishlist not found with id: " + id));

        WishlistEntity source = WishlistMapper.mapToWishlistEntity(dto);
        BeanUtils.copyProperties(source, existing, getNullPropertyNames(source));

        WishlistEntity saved = repository.save(existing);
        return WishlistMapper.mapToWishlistDto(saved);
    }

    private WishlistDto findByIdInternal(Long id) {
        WishlistEntity entity = repository.findById(id)
                .orElseThrow(() -> new ResourceNotFoundException("Wishlist not found with id: " + id));
        return WishlistMapper.mapToWishlistDto(entity);
    }

    private List<WishlistDto> getAllInternal() {
        return repository.findAll().stream().map(WishlistMapper::mapToWishlistDto).toList();
    }

    private String[] getNullPropertyNames(Object source) {
        BeanWrapper src = new BeanWrapperImpl(source);
        PropertyDescriptor[] pds = src.getPropertyDescriptors();

        Set<String> emptyNames = new HashSet<>();
        for (PropertyDescriptor pd : pds) {
            Object srcValue = src.getPropertyValue(pd.getName());
            if (srcValue == null) {
                emptyNames.add(pd.getName());
            }
        }
        return emptyNames.toArray(new String[0]);
    }
}

